<?php
/* 
 * The MIT License
 *
 * Copyright 2016 vConnect.dk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @category Magento
 * @package Vconnect_AllInOne
 * @author vConnect
 * @email kontakt@vconnect.dk
 * @class Vconnect_AllInOne_Model_System_Config_Backend_Shipping_License
 */

namespace Vconnect\Allinone\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Helper\Context 
     */
    protected $context;
    
    /**
     *
     * @var array 
     */
    protected $rates;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Vconnect\Allinone\Model\System\Config\Source\Carrier\Countries
     */
    protected $countries;

    /**
     * Magento 2.2.0 uses SerializerInterface to serialize data
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Data constructor.
     * @param Context $context
     * @param Countries $countries
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Vconnect\Allinone\Model\System\Config\Source\Carrier\Countries $countries,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->context = $context;
        $this->objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->jsonHelper = $jsonHelper;
        $this->countries = $countries;

        if (version_compare($productMetadata->getVersion(), '2.2.0', '>=')) {
            $this->serializer = $objectManager->get('\Magento\Framework\Serialize\SerializerInterface');
        }

        parent::__construct($context);
    }

    /**
     * @param string $path
     * @param integer $storeId
     * @return mixed
     */
    public function getStoreConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @param string $path
     * @param integer $storeId
     * @return bool
     */
    public function getStoreConfigFlag($path, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            $path, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Whethere a country code is scandianvian one
     * $param $countryCode 2 chars iso code
     * @return bool
     */
    public function isScandinavianCountry($countryCode)
    {
        return in_array($countryCode, $this->getScandinavianCountries());
    }

    /**
     * Get scandinavian countries
     * @return array
     */
    public function getScandinavianCountries()
    {
        return $this->countries->toArray();
    }

    /**
     * 
     * @param string $countryCode 2 chars iso code
     * @return bool
     */
    public function isEuCountry($countryCode)
    {
        return in_array(strtolower($countryCode), $this->getEuCountries());
    }

    /**
     * Return array of EU countries 2 chars code
     * @return array
     */
    public function getEuCountries()
    {
        $eu_countries = $this->getStoreConfig('general/country/eu_countries');
        return explode(',',  strtolower($eu_countries));
    }

    /**
     * 
     * @return array of all countries
     */
    public function getAllCountries()
    {
        $countrySourceModel = $this->objectManager->get('Magento\Directory\Model\Config\Source\Country');

        return array_map( function($item){
            return strtolower($item['value']);
        }, $countrySourceModel->toOptionArray(false));
    }

    /**
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getRatesForQuote(\Magento\Quote\Model\Quote $quote)
    {
        if(!$this->rates && $quote->getShippingAddress()){
            $this->rates = array();
            $rates_array = $quote->getShippingAddress()->getShippingRatesCollection()->toArray();
            $rates = array_slice($rates_array['items'],count($rates_array['items']) - $rates_array['totalRecords']);
            foreach ($rates as $rate){
                if(stripos($rate['code'],'vconnectpostnord') === false){
                    continue;
                }
                $config = $this->jsonHelper->jsonDecode($rate['vc_method_data']);

                $rate['sort_order'] = $this->getStoreConfig("carriers/{$config['system_path']}/sort_order");
                $rate['price_formated'] = $this->getPriceFormated($quote, $rate['price'], true);
                $rate['price_formated_withoutcontainer'] = $this->getPriceFormated($quote, $rate['price'], true, false);
                $this->rates[$rate['code']] = $rate;
            }
            usort($this->rates, function($a,$b){
                return (int)$a['sort_order'] - (int)$b['sort_order'];
            });
        }
        return $this->rates;
    }

    /**
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @param deciaml $price
     * @return string
     */
    public function getPriceFormated($quote, $price, $format = false, $includeContainer = true)
    {
        $taxHelper = $this->objectManager->get('Magento\Tax\Helper\Data'); 

        $displayTax = $taxHelper->displayShippingPriceIncludingTax();
        $_price = $taxHelper->getShippingPrice($price,$displayTax,$quote->getShippingAddress());
        $formated_price = $quote->getStore()->getBaseCurrency()->convert($_price);

        if ($format) {
            $formated_price = $quote->getStore()->getBaseCurrency()->format($formated_price, array(), $includeContainer);
        }

        return $formated_price;
    }

    /**
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @param deciaml $price
     * @return string
     */
    public function getPriceFormatedByOrder($order, $price, $format = false, $includeContainer = true)
    {
        $taxHelper = $this->objectManager->get('Magento\Tax\Helper\Data'); 

        $displayTax = $taxHelper->displayShippingPriceIncludingTax();
        $_price = $taxHelper->getShippingPrice($price,$displayTax,$order->getShippingAddress());
        $formated_price = $order->getStore()->getBaseCurrency()->convert($_price);

        if ($format) {
            $formated_price = $order->getStore()->getBaseCurrency()->format($formated_price, array(), $includeContainer);
        }

        return $formated_price;
    }

    /**
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $code
     * @return array|null
     */
    public function getRateDetailsByMethodCode(\Magento\Quote\Model\Quote $quote, $code)
    {
        $rates = $this->getRatesForQuote($quote);
        $result = array_filter( $rates,function($item) use($code){
            return $code == $item['code'];
        });
        return array_shift($result);
    }

    /**
     * 
     * @param string $code
     * @return string json string
     */
    public function getFelxDeliveryByMethodCode($code)
    {
        $content = $this->getRateDetailsByMethodCode($this->checkoutSession->getQuote(), $code);
        if(!isset($content['vc_method_data'])){
            return array();
        }
        $data = $this->jsonHelper->jsonDecode($content['vc_method_data']);
       
        return isset($data['delivery']['Flex Delivery'])?$data['delivery']['Flex Delivery']:array();
    }

    /**
     * 
     * @param string $system_path
     * @return string array
     */
    public function getAdditionalFeeData($system_path)
    {
        $data = array();

        if ($this->getStoreConfigFlag("carriers/{$system_path}/additional_fee_active")) {
            $data = array(
                'label'            => $this->getStoreConfig("carriers/{$system_path}/additional_fee_label"),
                'label_with_price' => $this->getStoreConfig("carriers/{$system_path}/additional_fee_label") . ' +' . $this->getPriceFormated($this->checkoutSession->getQuote(), (float)$this->getStoreConfig("carriers/{$system_path}/additional_fee_amount"), true, false),
                'price_base'       => (float)$this->getStoreConfig("carriers/{$system_path}/additional_fee_amount"),
                'price'            => $this->getPriceFormated($this->checkoutSession->getQuote(), (float)$this->getStoreConfig("carriers/{$system_path}/additional_fee_amount"), true),
            );
        }

        return $data;
    }

    public function getAdditionalfeeTitle($order = false) {
        if ($order) {
            if (!$order || !$order->getVconnectPostnordData()) {
                return false;
            }
            $vconnect_postnord_data = $this->jsonHelper->jsonDecode($order->getVconnectPostnordData());
        } else {
            $quote = $this->checkoutSession->getQuote();

            if (!$quote || !$quote->getVconnectPostnordData()) {
                return false;
            }
            $vconnect_postnord_data = $this->jsonHelper->jsonDecode($quote->getVconnectPostnordData());
        }

        if (!isset($vconnect_postnord_data['additional_fee_label'])) {
            return false;
        }

        return $vconnect_postnord_data['additional_fee_label'];
    }

    public function getAdditionalfeeAmount($order = false) {
        if ($order) {
            if (!$order || !$order->getVconnectPostnordData() || strpos($order->getShippingAddress()->getShippingMethod(), 'vconnectpostnord') === false || (strpos($order->getShippingAddress()->getShippingMethod(), 'privatehome') !== false)) {
                return false;
            }
            $vconnect_postnord_data = $this->jsonHelper->jsonDecode($order->getVconnectPostnordData());
        } else {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote || !$quote->getVconnectPostnordData() || strpos($quote->getShippingAddress()->getShippingMethod(), 'vconnectpostnord') === false || (strpos($quote->getShippingAddress()->getShippingMethod(), 'privatehome') !== false)) {
                return false;
            }
            $vconnect_postnord_data = $this->jsonHelper->jsonDecode($quote->getVconnectPostnordData());
        }

        if (!isset($vconnect_postnord_data['additional_fee_amount'])) {
            return false;
        }

        return $vconnect_postnord_data['additional_fee_amount'];
    }

    public function getUrl($route, $params) {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    public function serialize($data) {
        if (!empty($this->serializer)) {
            return $this->serializer->serialize($data);
        } else {
            return serialize($data);
        }
    }

    public function unserialize($data) {
        if (!empty($this->serializer)) {
            return $this->serializer->unserialize($data);
        } else {
            return unserialize($data);
        }
    }

    public function collectShippingMethodsForDK($countryId) {
        $methods = array();

        $destCountryId = strtolower($countryId);

        $shippingMethods = $this->getAvailableMethodsForDK();
        foreach ($shippingMethods as $data) {
            if (in_array($destCountryId, $data['countries']) &&
                !in_array($destCountryId,$data['exclude']) &&
                $this->getStoreConfigFlag("carriers/{$data['system_path']}/active")) {

                if ($destCountryId == 'dk' && $data['system_path'] == 'vconnect_postnord_home') {
                    $new_array = array('flexdelivery' => array(
                        'label'            => $this->getStoreConfig("carriers/vconnect_postnord_home/flex_delivery_label"),
                    ));
                    $data['arrival'] = $new_array + $data['arrival'];

                    $data['delivery']['Flex Delivery'] = array(
                        __('In front of the Door'),
                        __('Carport'),
                        __('Infront of backdoor'),
                        __('I have modttagarflex'),
                        __('Other place')
                    );
                }

                $m = new \Magento\Framework\DataObject($data);
                $m->setIdFieldName('method');
                $methods[] = $m;
            }
        }

        return $methods;
    }
    
    /**
     * 
     * @return array method configurations
     */
    public function getAvailableMethodsForDK()
    {
        $eupickup_countries = array();
        if ($this->getStoreConfigFlag("carriers/vconnect_postnord_eupickup/price")) {
            $vconnect_postnord_eupickup_price = $this->unserialize($this->getStoreConfig("carriers/vconnect_postnord_eupickup/price"));
            if (is_array($vconnect_postnord_eupickup_price)) {
                foreach ($vconnect_postnord_eupickup_price as $row) {
                    $countries = strtolower(str_replace(" ", "", $row['countries']));
                    if (!empty($countries)) {
                        $countries = explode(',', $countries);
                        foreach ($countries as $country) {
                            if (!in_array($country, $eupickup_countries)) {
                                $eupickup_countries[] = $country;
                            }
                        }
                    }
                }
            }
        }

        return array(
            array(
                'system_path'   => 'vconnect_postnord_home',
                'template'      => 'private', 
                'method'        => 'dk_privatehome',
                'countries'     => array('dk'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery'
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickup',
                'template'      => 'pickup',
                'method'        => 'dk_pickup',
                'countries'     => $this->getScandinavianCountries(),
                'exclude'       => array(),
                'multiprices'   => true,
            ),
            array(
                'system_path'   => 'vconnect_postnord_business',
                'template'      => 'commercial',
                'method'        => 'dk_commercial',
                'countries'     => array('dk'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_eu',
                'template'      => 'dpdeu',
                'method'        => 'dk_dpdeu',
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_intl',
                'template'      => 'dpdinternational',
                'method'        => 'dk_dpdinternational',
                'countries'     => $this->getAllCountries(),
                'exclude'       => $this->getEuCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickupinshop',
                'template'      => 'pickupinshop',
                'method'        => 'dk_pickupinshop',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_eupickup',
                'template'      => 'eupickup',
                'method'        => 'dk_eupickup',
                'countries'     => $eupickup_countries,
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
        );
    }

    public function collectShippingMethodsForSE($countryId)
    {
        $methods = array();

        $destCountryId = strtolower($countryId);

        $shippingMethods = $this->getAvailableMethodsForSE();
        foreach ($shippingMethods as $data)
        {
            if (in_array($destCountryId, $data['countries']) &&
                    !in_array($destCountryId, $data['exclude']) &&
                    $this->getStoreConfigFlag("carriers/{$data['system_path']}/active")) {

                if ($destCountryId == 'se' && $data['system_path'] == 'vconnect_postnord_home') {
                    $new_array = array('flexdelivery' => array(
                        'label'            => $this->getStoreConfig("carriers/vconnect_postnord_home/flex_delivery_label"),
                    ));
                    $data['arrival'] = $new_array + $data['arrival'];

                    $data['delivery']['Flex Delivery'] = array(
                        __('Lämna paketet utanför dörren'),
                        __('Utanför dörren'),
                        __('I garage'),
                        __('På baksidan'),
                    );
                }

                $m = new \Magento\Framework\DataObject($data);
                $m->setIdFieldName('method');
                $methods[] = $m;
            }
        }

        return $methods;
    }

    /**
     * 
     * @return array method configurations
     */
    public function getAvailableMethodsForSE()
    {
        $eupickup_countries = array();
        if ($this->getStoreConfigFlag("carriers/vconnect_postnord_eupickup/price")) {
            $vconnect_postnord_eupickup_price = $this->unserialize($this->getStoreConfig("carriers/vconnect_postnord_eupickup/price"));
            if (is_array($vconnect_postnord_eupickup_price)) {
                foreach ($vconnect_postnord_eupickup_price as $row) {
                    $countries = strtolower(str_replace(" ", "", $row['countries']));
                    if (!empty($countries)) {
                        $countries = explode(',', $countries);
                        foreach ($countries as $country) {
                            if (!in_array($country, $eupickup_countries)) {
                                $eupickup_countries[] = $country;
                            }
                        }
                    }
                }
            }
        }

        return array(
             array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'se_valuemail',
                'countries'     => array('se'),
                'exclude'       => array(),
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(
                    'Nearest pickuplocation' => 'Nearest pickuplocation'
                ),
                'multiprices'   => false,
                'price_code'    => 'price_se',
                'delivery_time' => '2-3 days',
            ),
             array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'se_privatehome',
                'countries'     => array('dk'),
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price_dk',
                'delivery_time' => '2-3 days',
            ),
            array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'se_private',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => array('no'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price_no',
                'delivery_time' => '2-3 days',
            ),
            array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'se_private',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price_eu',
                'delivery_time' => '2-3 days',
            ),
             array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'se_private',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => $this->getAllCountries(),
                'exclude'       => array_merge($this->getEuCountries(), $this->getScandinavianCountries()),
                'multiprices'   => false,
                'price_code'    => 'price_intl',
                 'delivery_time'=> '2-3 days',
            ),
            array(
                'system_path'   => 'vconnect_postnord_home',
                'template'      => 'private',
                'method'        => 'se_privatehome',
                'countries'     => array('se'),
                'exclude'       => array(),
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text")
                ),
                'delivery'      => array(
                    'Med kvittens'=>'Med kvittens',
                    'Utan kvittens (paketet ställs utanför dörren)'=>'Utan kvittens (paketet ställs utanför dörren)',
                ),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickup',
                'template'      => 'pickup',
                'method'        => 'se_pickup',
                'countries'     => $this->getScandinavianCountries(),
                'exclude'       => array(),
                'multiprices'   => true,
            ),
            array(
                'system_path'   => 'vconnect_postnord_business',
                'template'      => 'commercial',
                'method'        => 'se_commercial',
                'countries'     => array('se'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_eu',
                'template'      => 'dpdeu',
                'method'        => 'se_dpdclassic',
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_intl',
                'template'      => 'dpdinternational',
                'method'        => 'se_dpdclassic',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array_merge($this->getEuCountries(), $this->getScandinavianCountries()),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickupinshop',
                'template'      => 'pickupinshop',
                'method'        => 'se_pickupinshop',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_eupickup',
                'template'      => 'eupickup',
                'method'        => 'dk_eupickup',
                'countries'     => $eupickup_countries,
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
        );
    }

    public function collectShippingMethodsForNO($countryId)
    {
        $methods = array();

        $destCountryId = strtolower($countryId);

        $shippingMethods = $this->getAvailableMethodsForNO();
        foreach ($shippingMethods as $data)
        {
            if (in_array($destCountryId, $data['countries']) &&
                    !in_array($destCountryId, $data['exclude']) &&
                    $this->getStoreConfigFlag("carriers/{$data['system_path']}/active")) {

                if ($data['system_path'] == 'vconnect_postnord_home' &&
                        $this->getStoreConfigFlag("carriers/vconnect_postnord_home/flex_delivery_active")) {
                    $data['delivery']['Flex Delivery'] = array(
                        __('In front of the Door'),
//                        __('Carport'),
//                        __('Infront of backdoor'),
//                        __('I have modttagarflex'),
//                        __('Other place')
                    );
                }

                $m = new \Magento\Framework\DataObject($data);
                $m->setIdFieldName('method');
                $methods[] = $m;
            }
        }

        return $methods;
    }
    
    /**
     * 
     * @return array method configurations
     */
    public function getAvailableMethodsForNO()
    {
        $eupickup_countries = array();
        if ($this->getStoreConfigFlag("carriers/vconnect_postnord_eupickup/price")) {
            $vconnect_postnord_eupickup_price = $this->unserialize($this->getStoreConfig("carriers/vconnect_postnord_eupickup/price"));
            if (is_array($vconnect_postnord_eupickup_price)) {
                foreach ($vconnect_postnord_eupickup_price as $row) {
                    $countries = strtolower(str_replace(" ", "", $row['countries']));
                    if (!empty($countries)) {
                        $countries = explode(',', $countries);
                        foreach ($countries as $country) {
                            if (!in_array($country, $eupickup_countries)) {
                                $eupickup_countries[] = $country;
                            }
                        }
                    }
                }
            }
        }

        return array(
            array(
                'system_path'   => 'vconnect_postnord_home',
                'template'      => 'private', 
                'method'        => 'no_private',
                'countries'     => array('no'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_info' => 'You will have the opportunity to choose the time of delivery as soon as we have received your package',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'no_privatehome',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => array('no'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price_no',
                'delivery_info' => 'The package comes the same place as your local newspaper, either in your mailbox or on the doormat.',
            ),
            array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'no_privatehome',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price_eu',
            ),
            array(
                'system_path'   => 'vconnect_postnord_mailbox',
                'template'      => 'mailbox',
                'method'        => 'no_privatehome',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_mailbox/arrival_option_text")
                ),
                'delivery'      => array(),
                'countries'     => $this->getAllCountries(),
                'exclude'       => array_merge($this->getEuCountries(), $this->getScandinavianCountries()),
                'multiprices'   => false,
                'price_code'    => 'price_intl',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickup',
                'template'      => 'pickup',
                'method'        => 'no_pickup',
                'countries'     => $this->getScandinavianCountries(),
                'exclude'       => array(),
                'multiprices'   => true,
            ),
            array(
                'system_path'   => 'vconnect_postnord_business',
                'template'      => 'commercial',
                'method'        => 'no_commercial',
                'countries'     => array('no'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_eu',
                'template'      => 'dpdeu',
                'method'        => 'no_dpdclassic',
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_intl',
                'template'      => 'dpdinternational',
                'method'        => 'no_dpdclassic',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array_merge($this->getEuCountries(), $this->getScandinavianCountries()),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickupinshop',
                'template'      => 'pickupinshop',
                'method'        => 'no_pickupinshop',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_eupickup',
                'template'      => 'eupickup',
                'method'        => 'dk_eupickup',
                'countries'     => $eupickup_countries,
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
        );
    }

    public function collectShippingMethodsForFI($countryId)
    {
        $methods = array();

        $destCountryId = strtolower($countryId);

        $shippingMethods = $this->getAvailableMethodsForFI();
        foreach ($shippingMethods as $data)
        {
            if (in_array($destCountryId, $data['countries']) &&
                    !in_array($destCountryId, $data['exclude']) &&
                    $this->getStoreConfigFlag("carriers/{$data['system_path']}/active")) {

                $m = new \Magento\Framework\DataObject($data);
                $m->setIdFieldName('method');
                $methods[] = $m;
            }
        }

        return $methods;
    }

    /**
     * 
     * @return array method configurations
     */
    public function getAvailableMethodsForFI()
    {
        $eupickup_countries = array();
        if ($this->getStoreConfigFlag("carriers/vconnect_postnord_eupickup/price")) {
            $vconnect_postnord_eupickup_price = $this->unserialize($this->getStoreConfig("carriers/vconnect_postnord_eupickup/price"));
            if (is_array($vconnect_postnord_eupickup_price)) {
                foreach ($vconnect_postnord_eupickup_price as $row) {
                    $countries = strtolower(str_replace(" ", "", $row['countries']));
                    if (!empty($countries)) {
                        $countries = explode(',', $countries);
                        foreach ($countries as $country) {
                            if (!in_array($country, $eupickup_countries)) {
                                $eupickup_countries[] = $country;
                            }
                        }
                    }
                }
            }
        }

        return array(
            array(
                'system_path'   => 'vconnect_postnord_home',
                'template'      => 'private', 
                'method'        => 'fi_privatehome',
                'countries'     => array('fi'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_home/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickup',
                'template'      => 'pickup',
                'method'        => 'fi_pickup',
                'countries'     => $this->getScandinavianCountries(),
                'exclude'       => array(),
                'multiprices'   => true,
            ),
            array(
                'system_path'   => 'vconnect_postnord_business',
                'template'      => 'commercial',
                'method'        => 'fi_commercial',
                'countries'     => array('fi'),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
                'delivery_time' => '1-3 days',
                'arrival'      => array(
                    $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text") => $this->getStoreConfig("carriers/vconnect_postnord_business/arrival_option_text")
                ),
                'delivery'      => array(
                    'Personal Delivery'=>'Personal Delivery',
                ),
            ),
            array(
                'system_path'   => 'vconnect_postnord_eu',
                'template'      => 'dpdeu',
                'method'        => 'fi_dpdclassic',
                'countries'     => $this->getEuCountries(),
                'exclude'       => $this->getScandinavianCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_intl',
                'template'      => 'dpdinternational',
                'method'        => 'fi_dpdclassic',
                'countries'     => $this->getAllCountries(),
                'exclude'       => $this->getEuCountries(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_pickupinshop',
                'template'      => 'pickupinshop',
                'method'        => 'fi_pickupinshop',
                'countries'     => $this->getAllCountries(),
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
            array(
                'system_path'   => 'vconnect_postnord_eupickup',
                'template'      => 'eupickup',
                'method'        => 'dk_eupickup',
                'countries'     => $eupickup_countries,
                'exclude'       => array(),
                'multiprices'   => false,
                'price_code'    => 'price',
            ),
        );
    }
}
