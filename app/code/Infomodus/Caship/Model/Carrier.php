<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Caship\Model;

use Datetime;
use Infomodus\Caship\Helper\Config;
use Infomodus\Caship\Model\ResourceModel\Items\Collection;
use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Xml\Security;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * UPS shipping implementation
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'caship';

    protected $_countryFactory;

    protected $_isFixed = false;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;


    protected $_request;

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $_handy = [];
    protected $modelUPS;
    protected $modelDHL;
    protected $modelFedex;
    protected $_storeManage;
    protected $_cart;
    protected $_session;
    protected $_customerSession;
    protected $_appState;
    protected $_adminSession;


    private $ratesUps = null;
    private $ratesUpsWithNR = null;
    private $ratesUpsWithoutNR = null;
    private $ratesDHL = null;
    private $ratesFedex = null;
    private $isTimeInTransit = null;
    private $_conf = null;
    protected $orderAmount = null;
    protected $_lbl = null;
    protected $_prms = null;
    protected $allowedCurrencies;
    private $_coreRegistry = null;
    private $methodItems = null;
    private $product = null;
    private $countPackages = 0;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param ResultFactory $trackFactory
     * @param ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManage
     * @param Cart $cart
     * @param \Magento\Checkout\Model\Session $session
     * @param \Infomodus\Caship\Helper\Ups $handyUPS
     * @param Ups $modelUPS
     * @param \Infomodus\Caship\Helper\Dhl $handyDHL
     * @param Dhl $modelDHL
     * @param \Infomodus\Caship\Helper\Fedex $handyFedex
     * @param Fedex $modelFedex
     * @param Config $config
     * @param Session $customerSession
     * @param State $appState
     * @param Quote $adminSession
     * @param Registry $registry
     * @param ResourceModel\Items\Collection $methodItems
     * @param ProductRepository $product
     * @param TimezoneInterface $timezone
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ResultFactory $trackFactory,
        ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManage,
        Cart $cart,
        \Magento\Checkout\Model\Session $session,
        \Infomodus\Caship\Helper\Ups $handyUPS,
        Ups $modelUPS,
        \Infomodus\Caship\Helper\Dhl $handyDHL,
        Dhl $modelDHL,
        \Infomodus\Caship\Helper\Fedex $handyFedex,
        Fedex $modelFedex,
        Config $config,
        Session $customerSession,
        State $appState,
        Quote $adminSession,
        Registry $registry,
        Collection $methodItems,
        ProductRepository $product,
        TimezoneInterface $timezone,
        array $data = []
    )
    {
        $this->_handy['ups'] = $handyUPS;
        $this->modelUPS = $modelUPS;
        $this->_handy['dhl'] = $handyDHL;
        $this->modelDHL = $modelDHL;
        $this->_handy['fedex'] = $handyFedex;
        $this->modelFedex = $modelFedex;
        $this->_conf = $config;
        $this->_storeManage = $storeManage;
        $this->_cart = $cart;
        $this->_session = $session;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        $this->_adminSession = $adminSession;
        $this->_coreRegistry = $registry;
        $this->_countryFactory = $countryFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->methodItems = $methodItems;
        $this->product = $product;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateResultFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $this->timezone = $timezone;
    }

    protected function _doShipmentRequest(DataObject $request)
    {
        return new \Magento\Framework\DataObject();
    }

    public function processAdditionalValidation(DataObject $request)
    {
        return $this;
    }

    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this;
    }

    /**
     * Collect and get rates/errors
     *
     * @param RateRequest $request
     * @return  Result|Error|bool
     */
    public function collectRates(RateRequest $request)
    {
        $storeId = $this->_storeManage->getStore()->getId();
        if ($this->_conf->getStoreConfig('carriers/caship/active', $storeId) == 0) {
            return false;
        }

        if ($this->_coreRegistry->registry('isCashipGotRates') !== null) {
            return $this->_coreRegistry->registry('isCashipGotRates');
        }

        $this->_request = $request;

        /**
         * @var \Magento\Shipping\Model\Rate\Result $result
         */
        $result = $this->_rateResultFactory->create();

        $cartQuote = $this->_cart->getQuote();
        $quantity = $cartQuote->getItemsCount();
        $orderAmount = $cartQuote->getBaseSubtotal();
        $weight = $request->getPackageWeight();
        $zip = $request->getDestPostcode();

        $this->orderAmount = $orderAmount;

        $userGroupId = 0;
        if ($this->_appState->getAreaCode() == Area::AREA_ADMINHTML) {
            $userGroupId = $this->_adminSession->getQuote()->getCustomer()->getGroupId();
        } elseif ($this->_customerSession->isLoggedIn()) {
            $userGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();

        $model = $this->methodItems->addFieldToFilter(['is_store_all', 'store_id'], [['eq' => 0], [
                [
                    ['like' => '%,' . $storeId . ',%'],
                    ['like' => '%,' . $storeId],
                    ['like' => $storeId . ',%'],
                    ['like' => $storeId],
                ]
            ]
            ]
        )->addFieldToFilter(['negotiated', 'negotiated_amount_from', 'dinamic_price', 'company_type'], [
                ['eq' => 0],
                ['lteq' => $orderAmount],
                ['eq' => 0],
                [['neq' => 'ups'], ['neq' => 'upsinfomodus']],
            ]
        )
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('amount_min', [['lteq' => $orderAmount], ['eq' => 0]])
            ->addFieldToFilter('amount_max', [['gteq' => $orderAmount], ['eq' => 0]])
            ->addFieldToFilter('weight_min', [['lteq' => $weight], ['eq' => 0]])
            ->addFieldToFilter('weight_max', [['gteq' => $weight], ['eq' => 0]])
            ->addFieldToFilter('qty_min', [['lteq' => $quantity], ['eq' => 0]])
            ->addFieldToFilter('qty_max', [['gteq' => $quantity], ['eq' => 0]])
            ->addFieldToFilter('zip_min', [['lteq' => $zip], ['eq' => ''], ['null' => true]])
            ->addFieldToFilter('zip_max', [['gteq' => $zip], ['eq' => ''], ['null' => true]])
            ->addFieldToFilter(['is_country_all', 'country_ids'], [['eq' => 0], ['like' => '%' . $request->getDestCountryId() . '%']])
            ->addFieldToFilter('user_group_ids', [['eq' => ''], ['null' => true], ['like' => "%," . $userGroupId . ",%"]]);

        foreach ($model as $method) {
            if ($request->getDestCountryId() && ($request->getDestPostcode() || ($request->getDestRegionCode() && $request->getDestCity()))) {
                $methodEnd = $this->_getStandardShippingRate($request, $method, $orderAmount);
                if ($methodEnd !== false) {
                    if ($methodEnd->getMethod()) {
                        $result->append($methodEnd);
                    } else {
                        return $methodEnd;
                    }
                }
            }
        }

        if ($this->_coreRegistry->registry('isCashipGotRates') === null) {
            $this->_coreRegistry->register('isCashipGotRates', $result);
        }

        return $result;
    }

    protected function _getStandardShippingRate(RateRequest $request, $method, $orderAmount)
    {
        if (strlen($method->getIsProdAllow()) > 0) {
            foreach ($request->getAllItems() as $item) {
                if (!$item->isDeleted() && !$item->getParentItem()) {
                    $productOrigin = $this->product->getById($item->getProduct()->getId())->getData();
                    if (isset($productOrigin[$method->getIsProdAllow()]) && $productOrigin[$method->getIsProdAllow()] == 1) {
                        return false;
                    }
                }
            }
        }

        $storeId = $this->_storeManage->getStore()->getId();

        $rate = $this->_rateMethodFactory->create();
        $rate->setCarrier($this->_code);

        if (strlen($this->_conf->getStoreConfig('carriers/caship/title', $storeId)) > 0) {
            $rate->setCarrierTitle($this->_conf->getStoreConfig('carriers/caship/title', $storeId));
        }

        $mTitle = __($method->getName());
        $ratePrice = false;
        $rateCurrency = $this->_storeManage->getStore()->getCurrentCurrency()->getCode();
        $rate->setMethod($method->getId());
        if ($request->getFreeShipping() == true && $method->getFreeShipping() == 1) {
            $ratePrice = 0;
        } else {
            if ($method->getDinamicPrice() == 1) {
                if ($method->getCompanyType() == 'ups' || $method->getCompanyType() == 'upsinfomodus') {
                    if (($this->ratesUpsWithNR === null && $method->getNegotiated() == 1)
                        || ($this->ratesUpsWithoutNR === null && $method->getNegotiated() == 0)) {
                        $lbl = $this->modelUPS;

                        $lbl->invoiceLineTotal = $this->orderAmount;
                        $lbl->currency = $this->_conf->getStoreConfig('currency/options/base', $storeId);

                        $prms['shiptostateprovincecode'] = $request->getDestRegionCode();
                        $prms['shiptopostalcode'] = $request->getDestPostcode();
                        $prms['shiptocountrycode'] = $request->getDestCountryId();
                        $prms['shiptocity'] = $request->getDestCity();
                        $prms['shiptocompany'] = $request->getDestCompany();
                        $prms['shiptoaddressline'] = $request->getDestStreet();

                        $this->_handy['ups']->storeId = $storeId;
                        $lbl = $this->_handy['ups']->setParams($lbl, $prms, $request, $method);
                        $this->countPackages = count($lbl->packages);
                    }

                    if ($this->ratesUpsWithNR === null && $method->getNegotiated() == 1) {
                        $this->ratesUpsWithNR = $this->_handy['ups']->getShipRate($lbl, 1);
                    }

                    if ($this->ratesUpsWithoutNR === null && $method->getNegotiated() == 0) {
                        $this->ratesUpsWithoutNR = $this->_handy['ups']->getShipRate($lbl, 0);
                    }

                    if ($method->getNegotiated() == 1) {
                        $this->ratesUps = $this->ratesUpsWithNR;
                        if (isset($this->ratesUps['error'])) {
                            $this->ratesUpsWithNR = null;
                        }
                    } else {
                        $this->ratesUps = $this->ratesUpsWithoutNR;
                        if (isset($this->ratesUps['error'])) {
                            $this->ratesUpsWithoutNR = null;
                        }
                    }

                    if (isset($this->ratesUps[$method->getUpsmethodId()])) {
                        $ratecode2 = $this->ratesUps[$method->getUpsmethodId()];
                        if (isset($ratecode2['def'])) {
                            $nameOfPriceType = 'def';

                            if ($method->getNegotiated() == 1) {
                                if ($method->getTax() == 1 && isset($ratecode2['nrtax'])) {
                                    $nameOfPriceType = 'nrtax';
                                } else {
                                    $nameOfPriceType = 'nr';
                                }
                            } else {
                                if ($method->getTax() == 1 && isset($ratecode2['deftax'])) {
                                    $nameOfPriceType = 'deftax';
                                }
                            }

                            if (isset($ratecode2[$nameOfPriceType])) {
                                $ratePrice = (float)$ratecode2[$nameOfPriceType]['price'];
                                $rateCurrency = (string)$ratecode2[$nameOfPriceType]['currency'];

                                if ($method->getRural() == 0 && !empty($ratecode2['rural']['price'])) {
                                    $ratePrice -= ((float)$ratecode2['rural']['price']);
                                }
                            } else {
                                if ($this->_conf->getStoreConfig('carriers/caship/debug') == 1) {
                                    $this->_conf->log("Caship Error: Price type " . $nameOfPriceType . " does not exist");
                                    $this->_conf->log("Caship Error: Price type ", $this->ratesUps);
                                }

                                return false;
                            }
                        } else {
                            if ($this->_conf->getStoreConfig('carriers/caship/debug') == 1) {
                                $message = $this->ratesUps;
                                $this->_conf->log('Caship Error price', $message);
                                $this->_conf->log('Caship Error params', $prms);
                                $this->_conf->log('Caship Error method', $method->getData());
                            }

                            return false;
                        }
                    } else {
                        if ($this->_conf->getStoreConfig('carriers/caship/debug') == 1) {
                            $message = $this->ratesUps;
                            $this->_conf->log('Caship Error carriers rates', $message);
                            $this->_conf->log('Caship Error carriers method', $method->getData());
                        }

                        return false;
                    }
                } elseif ($method->getCompanyType() == 'dhl' || $method->getCompanyType() == 'dhlinfomodus') {
                    $prms['shiptocity'] = $request->getDestCity();
                    $prms['stateprovincecode'] = $request->getDestRegionCode();
                    $prms['shiptopostalcode'] = $request->getDestPostcode();
                    $prms['shiptocountrycode'] = $request->getDestCountryId();
                    $prms['declared_value'] = $this->currencyInvertConvert($rateCurrency, $orderAmount);
                    if (is_object($prms['declared_value'])) {
                        return $prms['declared_value'];
                    }

                    if ($this->ratesDHL === null) {
                        $lbl = $this->modelDHL;

                        $this->_handy['dhl']->storeId = $storeId;
                        $lbl = $this->_handy['dhl']->setParams($lbl, $prms, $request, $method);
                        $this->countPackages = count($lbl->packages);
                        $this->ratesDHL[0] = $this->_handy['dhl']->getShipRate($lbl, true, $prms['declared_value'], $rateCurrency);
                        $this->ratesDHL[1] = $this->_handy['dhl']->getShipRate($lbl, false);
                    }

                    if ($method->getCompanyType() == 'dhlinfomodus') {
                        $testing = $this->_conf->getStoreConfig('dhllabel/testmode/testing', $storeId);
                    } else {
                        $testing = ('https://xmlpi-ea.dhl.com/XMLShippingServlet' == $this->_conf->getStoreConfig('carriers/dhl/gateway_url', $storeId) ? 0 : 1);
                    }

                    if (!empty($this->ratesDHL) > 0) {
                        if (is_array($this->ratesDHL[0]) && count($this->ratesDHL[0]) > 0) {
                            foreach ($this->ratesDHL[0] as $k => $price) {
                                if ($price->getProductGlobalCode() == $method->getDhlmethodId() && ($price->getTotalAmount() > 0 || $testing == 1)) {
                                    $ratePrice = $price->getTotalAmount();
                                    $rateCurrency = $price->getCurrencyCode();
                                    break;
                                }
                            }
                        }

                        if (is_array($this->ratesDHL[1]) && count($this->ratesDHL[1]) > 0) {
                            foreach ($this->ratesDHL[1] as $k => $price) {
                                if ($price->getProductGlobalCode() == $method->getDhlmethodId() && ($price->getTotalAmount() > 0 || $testing == 1)) {
                                    $ratePrice = $price->getTotalAmount();
                                    $rateCurrency = $price->getCurrencyCode();
                                    break;
                                }
                            }
                        }
                    } else {
                        return false;
                    }
                } elseif ($method->getCompanyType() == 'fedex' || $method->getCompanyType() == 'fedexinfomodus') {
                    if ($this->ratesFedex === null) {
                        $lbl = $this->modelFedex;
                        $prms['shiptocity'] = $request->getDestCity();
                        $prms['shiptopostalcode'] = $request->getDestPostcode();
                        $prms['shiptocountrycode'] = $request->getDestCountryId();
                        $prms['declared_value'] = $orderAmount;

                        $this->_handy['fedex']->storeId = $storeId;
                        $lbl = $this->_handy['fedex']->setParams($lbl, $prms, $request, $method);
                        $this->countPackages = count($lbl->packages);
                        $this->ratesFedex = $this->_handy['fedex']->getShipRate($lbl);

                    }

                    if (!isset($this->ratesFedex['error'])) {
                        foreach ($this->ratesFedex as $k => $price) {
                            if ($k == $method->getFedexmethodId() && $price['price'] > 0) {
                                $ratePrice = (float)str_replace(",", "", $price['price']);
                                $rateCurrency = $price['currency'];
                                break;
                            }
                        }
                    } else {
                        return false;
                    }
                }

                if ($ratePrice !== false && $method->getAddedValue() != 0 && $method->getAddedValue() != "") {
                    $coefficient = 1;

                    if ($method->getAddedValueAppliedFor() == 'package' && !empty($this->countPackages)) {
                        $coefficient = $this->countPackages;
                    }

                    if ($method->getAddedValueType() == 'static') {
                        $ratePrice += (float)str_replace(",", ".", $method->getAddedValue()) * $coefficient;
                    } else {
                        $ratePrice += (($ratePrice / 100) * str_replace(",", ".", $method->getAddedValue())) * $coefficient;
                    }
                }
            } else {
                $ratePrice = $method->getPrice();
                $rateCurrency = $this->_request->getBaseCurrency()->getCode();
            }
        }

        if ($method->getCompanyType() == 'ups' || $method->getCompanyType() == 'upsinfomodus') {
            if ($method->getTimeInTransit() == 1) {
                if ($this->isTimeInTransit == null) {
                    $lbl = $this->modelUPS;

                    $lbl->invoiceLineTotal = $this->orderAmount;
                    $lbl->currency = $this->_conf->getStoreConfig('currency/options/base', $storeId);

                    $prms['shiptostateprovincecode'] = $request->getDestRegionCode();
                    $prms['shiptopostalcode'] = $request->getDestPostcode();
                    $prms['shiptocountrycode'] = $request->getDestCountryId();
                    $prms['shiptocity'] = $request->getDestCity();

                    $this->_handy['ups']->storeId = $storeId;
                    $lbl = $this->_handy['ups']->setParams($lbl, $prms, $request, $method);
                    $tineInTransit = $lbl->timeInTransit(0);
                    $this->isTimeInTransit = $tineInTransit;
                } else {
                    $tineInTransit = $this->isTimeInTransit;
                }

                $tineInTransit = isset($tineInTransit['days'][$method->getUpsmethodId()]) ? $tineInTransit['days'][$method->getUpsmethodId()] : false;

                if ($tineInTransit) {
                    if ($method->getTitShowFormat() === 'days') {
                        $mTitle .= ' (' . ($tineInTransit['days'] + $method->getAddDay()) . __(' day(s)') . ')';
                    } else {
                        $timezone = new \DateTimeZone($this->_conf->getStoreConfig('general/locale/timezone', $storeId));
                        $dateFormat = new DateTime($tineInTransit['datetime']['date'], $timezone);
                        if ($method->getTitShowFormat() === 'datetime') {
                            $mTitle .= ' (' . $dateFormat->format('d') . ' ' . __($dateFormat->format('F')) . ' ' . $dateFormat->format('Y') . ' ' . substr($tineInTransit['datetime']['time'], 0, -3) . ')';
                        } else if ($method->getTitShowFormat() === 'full') {
                            /*$locale = $request->getDestCountryId();*/
                            $locale = $this->_conf->getStoreConfig('general/locale/code', $storeId);
                            $mTitle .= ' ' . $this->timezone->formatDate($this->timezone->date($dateFormat->getTimestamp(), $locale, true, false), \IntlDateFormatter::FULL);
                        }
                    }
                }
            }
        }

        $rate->setMethodTitle($mTitle);
        if ($ratePrice === false) {
            return false;
        }

        $ratePrice = $this->currencyConvert($rateCurrency, $ratePrice);
        if (is_object($ratePrice)) {
            return $ratePrice;
        }

        if ($ratePrice > 1 && $this->_conf->getStoreConfig('carriers/caship/price_format', $storeId) == 1) {
            $ratePrice = ceil($ratePrice * 10) / 10;
        }

        $rate->setPrice(round($ratePrice, $this->_conf->getStoreConfig('carriers/caship/price_format', $storeId)));
        $rate->setCost(0);

        return $rate;
    }

    public function currencyConvert($rateCurrency, $ratePrice)
    {
        $responseCurrencyCode = $this->mappingCurrencyCode($rateCurrency);
        if ($responseCurrencyCode) {
            if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                $ratePrice = (double)$ratePrice * $this->_getBaseCurrencyKoef($responseCurrencyCode);
            } else {
                $errorTitle = __(
                    'We can\'t convert a rate from "%1-%2".',
                    $responseCurrencyCode,
                    $this->_request->getPackageCurrency()->getCode()
                );
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errorTitle);
                return $error;
            }
        }

        return $ratePrice;
    }

    public function currencyInvertConvert($rateCurrency, $ratePrice)
    {
        $responseCurrencyCode = $this->mappingCurrencyCode($rateCurrency);
        if ($responseCurrencyCode) {
            if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                $ratePrice = (double)$ratePrice / $this->_getBaseCurrencyKoef($responseCurrencyCode);
            } else {
                $errorTitle = __(
                    'We can\'t convert a rate from "%1-%2".',
                    $responseCurrencyCode,
                    $this->_request->getPackageCurrency()->getCode()
                );
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errorTitle);
                return $error;
            }
        }

        return $ratePrice;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $storeId = $this->_storeManage->getStore()->getId();
        if ($this->_storeManage->getStore()->getCode() == 'admin') {
            $storeId = $this->_conf->getRequest()->getParam('store', 0);
            if ($storeId) {
                $code = $this->_conf->getStoreByCode($storeId);
                if ($code) {
                    $storeId = $code->getId();
                }
            }
        }

        $arrMethods = [];
        $model = $this->methodItems;
        if($storeId > 1) {
            $model->addFieldToFilter(['is_store_all', 'store_id'], [['eq' => 0], [
                    [
                        ['like' => '%,' . $storeId . ',%'],
                        ['like' => '%,' . $storeId],
                        ['like' => $storeId . ',%'],
                        ['like' => $storeId],
                    ]
                ]
                ]
            );
        }

        $model->addFieldToFilter('status', 1);
        foreach ($model as $method) {
            $arrMethods[$method->getId()] = $method->getTitle();
        }

        return $arrMethods;
    }

    protected function _getBaseCurrencyKoef($code)
    {
        $_baseCurrencyRate = 1;

        if ($code != $this->_request->getBaseCurrency()->getCode()) {
            $_baseCurrencyRate = $this->_currencyFactory->create()->load(
                $code
            )->getAnyRate(
                $this->_request->getBaseCurrency()->getCode()
            );
        }

        return $_baseCurrencyRate;
    }

    private function mappingCurrencyCode($code)
    {
        $currencyMapping = [
            'RMB' => 'CNY',
            'CNH' => 'CNY'
        ];

        return isset($currencyMapping[$code]) ? $currencyMapping[$code] : $code;
    }
}
