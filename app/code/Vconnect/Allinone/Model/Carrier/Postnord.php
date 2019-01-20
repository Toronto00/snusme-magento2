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
 * @class Vconnect_AllInOne_Model_Carrier_Postnord
 */

namespace Vconnect\Allinone\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Postnord extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'vconnectpostnord';

    protected $_code_method = 'vconnect_';

    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    /**
     * @var HelperData
     */
    protected $_dataHelper;

    /**
     * @var \Vconnect\Allinone\Api\RateRepositoryInterface
     */
    protected $_allinoneRateRepository;

    /**
     * @var \Vconnect\Allinone\Api\Data\RateInterfaceFactory
     */
    protected $_allinoneRateFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfiguration;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_dataCollection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Postnord constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Vconnect\Allinone\Helper\Data $dataHelper
     * @param \Vconnect\Allinone\Api\RateRepositoryInterface $allinoneRateRepository
     * @param \Vconnect\Allinone\Api\Data\RateInterfaceFactory $allinoneRateFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Data\Collection $dataCollection
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Vconnect\Allinone\Api\RateRepositoryInterface $allinoneRateRepository,
        \Vconnect\Allinone\Api\Data\RateInterfaceFactory $allinoneRateFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $dataCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_dataHelper = $dataHelper;
        $this->_allinoneRateRepository = $allinoneRateRepository;
        $this->_allinoneRateFactory = $allinoneRateFactory;
        $this->_productRepository = $productRepository;
        $this->_scopeConfiguration = $scopeConfiguration;
        $this->_checkoutSession = $checkoutSession;
        $this->_backendSession = $backendSession;
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_dataCollection = $dataCollection;
        $this->_objectManager = $objectManager;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'vconnect_postnord_private'       => $this->_dataHelper->getStoreConfig('carrier/vconnect_postnord_home/name'),
            'vconnect_postnord_commercial'    => $this->_dataHelper->getStoreConfig('carrier/vconnect_postnord_business/name'),
            'vconnect_postnord_pickup'        => $this->_dataHelper->getStoreConfig('carrier/vconnect_postnord_pickup/name'),
            'vconnect_postnord_mailbox'       => $this->_dataHelper->getStoreConfig('carrier/vconnect_postnord_mailbox/name'),
            'vconnect_postnord_pickupinshop'  => $this->_dataHelper->getStoreConfig('carrier/vconnect_postnord_pickupinshop/name'),
        );
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || !$this->getConfigFlag('license_status')) {
            return false;
        }
        if(!$request->getCountryId()){
            return $this->_logger->debug('PostNord: No origin country');
        }

        if (!$request->getDestCountryId()) {
            return $this->_logger->debug('PostNord: No Destination country');
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValueWithDiscount() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValueWithDiscount() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }

         // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        $result = $this->_rateResultFactory->create();

        $methods = array();

        if (strtolower($request->getCountryId()) == 'dk') {
            $methods = $this->_dataHelper->collectShippingMethodsForDK($request->getDestCountryId());
        } elseif (strtolower($request->getCountryId()) == 'se') {
            $methods = $this->_dataHelper->collectShippingMethodsForSE($request->getDestCountryId());
        } elseif (strtolower($request->getCountryId()) == 'no') {
            $methods = $this->_dataHelper->collectShippingMethodsForNO($request->getDestCountryId());
        } elseif (strtolower($request->getCountryId()) == 'fi') {
            $methods = $this->_dataHelper->collectShippingMethodsForFI($request->getDestCountryId());
        }

        if(empty($methods)){
            return false;
        }

        usort($methods, function($a,$b){
            if (isset($a['system_path']) && isset($b['system_path'])) {
                $a['sort_order'] = (int)$this->_dataHelper->getStoreConfig("carriers/{$a['system_path']}/sort_order");
                $b['sort_order'] = (int)$this->_dataHelper->getStoreConfig("carriers/{$b['system_path']}/sort_order");
                if ($a['sort_order'] == $b['sort_order']) {
                    return 0;
                }
                return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
            } else {
                return 0;
            }
        });

        $allVconnectAllinoneMethodData = array();
        foreach ($methods as $_method) {
            $method = $this->_createShippingMethodByCode($request, $freeQty, $_method);
            if (!$method) {
                continue;
            }

            $allinoneMethodData = array();
            if ($method->hasVcMethodData()) {
                $vconnectPostnordData = json_decode($method->getVcMethodData());
                $allVconnectAllinoneMethodData[$vconnectPostnordData->system_path] = $method->getData();
                $allVconnectAllinoneMethodData[$vconnectPostnordData->system_path]['code'] = $this->getCarrierCode() . '_' . $vconnectPostnordData->method;
                $allVconnectAllinoneMethodData[$vconnectPostnordData->system_path]['sort_order'] = $vconnectPostnordData->sort_order;
            }

            $result->append($method);
        }

        if (!empty($allVconnectAllinoneMethodData)) {
            $magentoDateObject = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');

            $allinoneRate = $this->_allinoneRateFactory->create();

            $collection = $allinoneRate->getCollection();
            $collection->addFieldToFilter('quote_id', array('eq' => $this->_checkoutSession->getQuote()->getId()));
            $collection->addFieldToFilter('address_id', array('eq' => $this->_checkoutSession->getQuote()->getShippingAddress()->getId()));

            $isRowExist = false;
            foreach ($collection as $item) {
                $isRowExist = true;
                $item->setRateData(serialize($allVconnectAllinoneMethodData));
                $item->save();
            }

            if (!$isRowExist) {
                $allinoneRate
                    ->setRateData(serialize($allVconnectAllinoneMethodData))
                    ->setQuoteId($this->_checkoutSession->getQuote()->getId())
                    ->setAddressId($this->_checkoutSession->getQuote()->getShippingAddress()->getId())
                    ->setDateCreated($magentoDateObject->gmtDate());
                    $this->_allinoneRateRepository->save($allinoneRate);
            }
        }

        return $result;
    }

    /**
     * Get price rate for order with specific weight and subtotal
     * @param float $orderPrice
     * @param float $orderWeight
     * @param \Magento\Framework\DataObject $config method configuration
     * @return float
     */
    public function getRate($orderPrice, $orderWeight, \Magento\Framework\DataObject $config)
    {
        $code = $config->getSystemPath();

        if($config->getMultiprices()){
            $ratePath = sprintf('carriers/%s/price_%s',$code, $config->getDestCountry());
        }else{
            $ratePath = sprintf('carriers/%s/%s',$code, $config->getPriceCode());
        }

        $result = $this->_dataHelper->getStoreConfig($ratePath);
        if(!$result){
            $this->_logger->debug("no rate for code $code and path $ratePath" );

            return FALSE;
        }
        $pickupShippingRates = $this->_dataHelper->unserialize($result);
        if (is_array($pickupShippingRates) && !empty($pickupShippingRates)) {
            foreach ($pickupShippingRates as $pickupShippingRate) {
                if( (float)$pickupShippingRate['orderminprice'] <= (float)$orderPrice
                        && ( (float)$pickupShippingRate['ordermaxprice'] >= (float)$orderPrice || (float)$pickupShippingRate['ordermaxprice'] == 0)
                        && (float)$pickupShippingRate['orderminweight'] <= (float)$orderWeight
                        && ( (float)$pickupShippingRate['ordermaxweight'] >= (float)$orderWeight || (float)$pickupShippingRate['ordermaxweight'] == 0)
                        ) {
                    return $pickupShippingRate;
                }
            }
        }
        return FALSE;
    }

    /**
     * Get price rate for order with specific weight and subtotal
     * @param float $orderPrice
     * @param float $orderWeight
     * @param Varien_Object $config method configuration
     * @return float
     */
    public function getRateForMethodEupickup($orderPrice, $orderWeight, \Magento\Framework\DataObject $config)
    {
        $code = $config->getSystemPath();

        if($config->getMultiprices()){
            $ratePath = sprintf('carriers/%s/price_%s',$code, $config->getDestCountry());
        }else{
            $ratePath = sprintf('carriers/%s/%s',$code, $config->getPriceCode());
        }

        $result = $this->_dataHelper->getStoreConfig($ratePath);
        if(!$result){
            $this->_logger->debug("no rate for code $code and path $ratePath" );

            return FALSE;
        }

        $pickupShippingRates = $this->_dataHelper->unserialize($result);
        if (is_array($pickupShippingRates) && !empty($pickupShippingRates)) {
            foreach ($pickupShippingRates as $pickupShippingRate) {
                $countries = strtoupper(str_replace(" ", "",$pickupShippingRate['countries']));
                $countries = explode(',', $countries);

                if((in_array(strtoupper($config->getDestCountry()), $countries) || in_array('*', $countries))
                        && (float)$pickupShippingRate['orderminprice'] <= (float)$orderPrice
                        && ( (float)$pickupShippingRate['ordermaxprice'] >= (float)$orderPrice || (float)$pickupShippingRate['ordermaxprice'] == 0)
                        && (float)$pickupShippingRate['orderminweight'] <= (float)$orderWeight
                        && ( (float)$pickupShippingRate['ordermaxweight'] >= (float)$orderWeight || (float)$pickupShippingRate['ordermaxweight'] == 0)
                        ) {
                    return $pickupShippingRate;
                }
            }
        }
        return FALSE;
    }

    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param string $code
     * @param float $freeQty
     * @param array $data Method data
     * @param Vconnect_AllInOne_Model_Carrier_Postnord $carrier carrier object
     * @return Mage_Shipping_Model_Rate_Result_Method|Bool
     */
    protected function _createShippingMethodByCode(RateRequest $request, $freeQty, \Magento\Framework\DataObject $data)
    {
        $code = $data->getSystemPath();
        $methodCode = $data->getMethod();

        if( !$this->_dataHelper->getStoreConfig("carriers/$code/active") ){
            return false;
        }

        $total = ($request->getBaseSubtotalInclTax() !== null ? $request->getBaseSubtotalInclTax() : $request->getPackageValueWithDiscount());

        $data->setCountry(strtolower($request->getCountryId()));
        $data->setDestCountry(strtolower($request->getDestCountryId()));

        if ($data->getSystemPath() == 'vconnect_postnord_eupickup') {
            $rate = $this->getRateForMethodEupickup($total, $request->getPackageWeight(), $data);

            $method_title = '';
            if (!empty($rate['title'])) {
                $method_title = trim($rate['title']);
            }
            $data->setMethodTitle($method_title);

            $transit_time = false;
            if (!empty($rate['transit_time'])) {
                $transit_time = trim($rate['transit_time']);
            }
            $data->setDeliveryTime($transit_time);
        } elseif ($data->getSystemPath() == 'vconnect_postnord_home') {
            $rate = $this->getRate($total, $request->getPackageWeight(), $data);

            // Option text method data
            $configOptionText = clone $data;
            $configOptionText->setPriceCode('arrival_option_text_price');
            $rateOptionText = $this->getRate($total, $request->getPackageWeight(), $configOptionText);
            if ($rateOptionText !== false) {
                $optionTextData = array(
                    'label'             => $this->_dataHelper->getStoreConfig("carriers/$code/arrival_option_text"),
                    'label_with_price'  => $this->_dataHelper->getStoreConfig("carriers/$code/arrival_option_text") . ' - ' . $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rateOptionText['price'], true, false),
                    'price'             => $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rateOptionText['price'], true, false),
                    'base_price'        => $rateOptionText['price'],
                );
                $data->setOptionTextData($optionTextData);
            }

            // Additional fee method data
            if ($this->_dataHelper->getStoreConfigFlag("carriers/$code/additional_fee_active")) {
                $configAdditionalFee = clone $data;
                $configAdditionalFee->setPriceCode('additional_fee_amount');
                $rateAdditionalFee = $this->getRate($total, $request->getPackageWeight(), $configAdditionalFee);
                if ($rateAdditionalFee !== false) {
                    $additionalFeeData = array(
                        'label'             => $this->_dataHelper->getStoreConfig("carriers/$code/additional_fee_label"),
                        'label_with_price'  => $this->_dataHelper->getStoreConfig("carriers/$code/additional_fee_label") . ' - ' . $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rateAdditionalFee['price'], true, false),
                        'price'             => $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rateAdditionalFee['price'], true, false),
                        'base_price'        => $rateAdditionalFee['price'],
                    );
                    $data->setAdditionalFeeData($additionalFeeData);
                }
            }

            // Flex delivery data
            if ($rate !== false) {
                $flexDeliveryData = array(
                    'label'             => $this->_dataHelper->getStoreConfig("carriers/$code/flex_delivery_label"),
                    'label_with_price'  => $this->_dataHelper->getStoreConfig("carriers/$code/flex_delivery_label") . ' - ' . $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rate['price'], true, false),
                    'price'             => $this->_dataHelper->getPriceFormated($this->_checkoutSession->getQuote(), $rate['price'], true, false),
                    'base_price'        => $rate['price'],
                );
                $data->setFlexDeliveryData($flexDeliveryData);
            }

            $data->setDeliveryTime($this->_dataHelper->getStoreConfig("carriers/$code/transit_time")?:false);
            $method_title = $this->_dataHelper->getStoreConfig("carriers/$code/name");

            $vconnectPostnordData = $this->_checkoutSession->getQuote()->getVconnectPostnordData();
            if ($vconnectPostnordData) {
                $vconnectPostnordData = json_decode($vconnectPostnordData);
                if (isset($vconnectPostnordData->additional_fee_amount)) {
                    $rate['price'] = $vconnectPostnordData->additional_fee_amount;
                }
            }
        } else {
            $rate = $this->getRate($total, $request->getPackageWeight(), $data);

            $data->setDeliveryTime($this->_dataHelper->getStoreConfig("carriers/$code/transit_time")?:false);
            $method_title = $this->_dataHelper->getStoreConfig("carriers/$code/name");
        }

        $method_description = $this->_dataHelper->getStoreConfig("carriers/$code/description");

        if ($rate === false) {
            $this->_logger->debug("No price rate for $code");
            return false;
        }
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setVcMethodData($data->toJson());
        $method->setMethod($methodCode);
        $method->setMethodTitle($method_title);
        $method->setMethodDescription($method_description);

        if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
            $shippingPrice = 0;
        } else {
            $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
        }

        $method->setPrice($shippingPrice)->setCost($rate['price']);

        return $method;
    }
}
