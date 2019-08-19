<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Upsap\Model;

use DateTime;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Xml\Security;

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
    const CODE = 'upsap';

    protected $_countryFactory;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
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
    protected $_handy;
    protected $_storeManage;
    protected $_cart;
    protected $_session;
    protected $_customerSession;
    protected $_appState;
    protected $_adminSession;
    protected $_conf;
    protected $ratesUps = null;
    protected $ratesUpsWithNR = null;
    protected $ratesUpsWithoutNR = null;
    protected $orderAmount = null;
    protected $_lbl = null;
    protected $_prms = null;
    protected $allowedCurrencies;
    private $_coreRegistry = null;
    private $timezone;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Config $configHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManage,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $session,
        \Infomodus\Upsap\Helper\Ups $handyUPS,
        \Infomodus\Upsap\Helper\Config $config,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote $adminSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    )
    {
        $this->_handy = $handyUPS;
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
        $this->timezone = $timezone;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $xmlSecurity, $xmlElFactory, $rateResultFactory, $rateMethodFactory, $trackFactory,
            $trackErrorFactory, $trackStatusFactory, $regionFactory, $countryFactory, $currencyFactory, $directoryData, $stockRegistry, $data);
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
    }

    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * Collect and get rates/errors
     *
     * @param RateRequest $request
     * @return  Result|Error|bool
     */
    public function collectRates(RateRequest $request)
    {
        if ($this->_coreRegistry->registry('isUpsapGotRates') !== null) {
            return $this->_coreRegistry->registry('isUpsapGotRates');
        }

        $this->_request = $request;

        $result = $this->_rateResultFactory->create();

        $storeId = $this->_storeManage->getStore()->getId();

        if ($this->_conf->getStoreConfig('carriers/upsap/active', $storeId) == 0) {
            return false;
        }

        $cartQuote = $this->_cart->getQuote();
        $quantity = $cartQuote->getItemsCount();
        $orderAmount = $cartQuote->getBaseSubtotal();
        $weight = $request->getPackageWeight();
        $zip = $request->getDestPostcode();

        $this->orderAmount = $orderAmount;

        $userGroupId = 0;

        if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $userGroupId = $this->_adminSession->getQuote()->getCustomer()->getGroupId();
        } elseif ($this->_customerSession->isLoggedIn()) {
            $userGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        $lbl = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Ups');

        $lbl->invoiceLineTotal = $this->orderAmount;
        $lbl->currency = $this->_conf->getStoreConfig('currency/options/base', $storeId);

        $prms['shiptostateprovincecode'] = $request->getDestRegionCode();
        $prms['shiptopostalcode'] = $request->getDestPostcode();
        $prms['shiptocountrycode'] = $request->getDestCountryId();
        $prms['shiptocity'] = $request->getDestCity();
        $prms['shiptocompany'] = $request->getDestCompany();
        $packages = [];
        $packages[0]['weight'] = $request->getPackageWeight();
        $packages[0]['packagingtypecode'] = $this->_conf->getStoreConfig('carriers/upsap/packagingtypecode', $storeId);
        $packages[0]['additionalhandling'] = $this->_conf->getStoreConfig('carriers/upsap/additionalhandling', $storeId) > 0 ? '<AdditionalHandling />' : '';

        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();

        if ($this->_conf->isModuleOutputEnabled("Infomodus_Upslabel")) {
            $packages[0]['packweight'] = round($this->_conf->getStoreConfig('upslabel/weightdimension/packweight', $storeId), 1) > 0 ? round($this->_conf->getStoreConfig('upslabel/weightdimension/packweight', $storeId), 1) : '0';

            /* Multi package */
            $dimensionSets = ObjectManager::getInstance()->get(\Infomodus\Upslabel\Model\Config\Defaultdimensionsset::class)->toOptionObjects();
            if (count($dimensionSets) > 0 || $this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $storeId) == 1) {
                $attributeCodeWidth = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_width', $storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_width', $storeId) : 'width';
                $attributeCodeHeight = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_height', $storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_height', $storeId) : 'height';
                $attributeCodeLength = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_length', $storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_length', $storeId) : 'length';

                try {
                    $countProductInBox = 0;
                    $dimensionsType = $this->_conf->getStoreConfig('upslabel/weightdimension/dimensions_type', $storeId);

                    $i = 0;
                    $packer = new \DVDoug\BoxPacker\Packer;

                    foreach ($request->getAllItems() as $item) {
                        if (!$item->isDeleted() && !$item->getParentItem()) {
                            $itemData = $item->getData();
                            if (!isset($itemData['qty']) && isset($itemData['qty_ordered'])) {
                                $itemData['qty'] = $itemData['qty_ordered'];
                            }

                            $myproduct = $this->_conf->_objectManager->get('Magento\Catalog\Model\Product')->load($itemData['product_id'])->getData();

                            $myproduct['weight'] = $item->getWeight();

                            for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                                if ($dimensionsType == 0) {
                                    $myproduct = $this->_conf->getProductSizes(
                                        $item,
                                        $myproduct,
                                        $packer,
                                        $attributeCodeWidth,
                                        $attributeCodeHeight,
                                        $attributeCodeLength
                                    );
                                    if ($myproduct === false) {
                                        $countProductInBox = 0;
                                        break;
                                    } else {
                                        if ($this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $storeId) == 1) {
                                            $packer->addBox(new \Infomodus\Upsap\Model\Packer\TestBox(
                                                'def_box',
                                                1000,
                                                1000,
                                                1000,
                                                0,
                                                1000,
                                                1000,
                                                1000,
                                                150
                                            ));
                                            $packedBoxes = $packer->pack();
                                            if (count($packedBoxes) > 0) {
                                                foreach ($packedBoxes as $packedBox) {
                                                    $packages[$i] = $packages[0];
                                                    $packages[$i]['width'] = $packedBox->getUsedWidth();
                                                    $packages[$i]['length'] = $packedBox->getUsedLength();
                                                    $packages[$i]['height'] = $packedBox->getUsedDepth();
                                                    $packages[$i]['weight'] = $packedBox->getWeight();
                                                    $i++;
                                                }
                                            }

                                            $packer = new \DVDoug\BoxPacker\Packer;
                                        } else {
                                            $countProductInBox++;
                                        }

                                    }
                                } else {
                                    $packer->addItem(
                                        new \Infomodus\Upsap\Model\Packer\TestItem(
                                            $myproduct['name'],
                                            1,
                                            1,
                                            1,
                                            $myproduct['weight'],
                                            true
                                        )
                                    );
                                }
                            }

                            if ($countProductInBox == 0 && $this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $storeId) == 0) {
                                break;
                            }
                        }
                    }

                    if ($countProductInBox > 0) {
                        foreach ($dimensionSets as $v) {
                            if (!empty($v)) {
                                $packer->addBox(new \Infomodus\Upsap\Model\Packer\TestBox(
                                    $v->getId(),
                                    $v->getOuterWidth(),
                                    $v->getOuterLengths(),
                                    $v->getOuterHeight(),
                                    $v->getEmptyWeight(),
                                    $v->getWidth(),
                                    $v->getLengths(),
                                    $v->getHeight(),
                                    $v->getMaxWeight()
                                ));
                            }
                        }

                        $packedBoxes = $packer->pack();
                        if (count($packedBoxes) > 0) {
                            foreach ($packedBoxes as $packedBox) {
                                $boxType = $packedBox->getBox();
                                $packages[$i] = $packages[0];
                                $packages[$i]['width'] = $boxType->getOuterWidth();
                                $packages[$i]['length'] = $boxType->getOuterLength();
                                $packages[$i]['height'] = $boxType->getOuterDepth();
                                $packages[$i]['weight'] = $packedBox->getWeight();
                                $i++;
                            }
                        }
                    }
                } catch
                (\DVDoug\BoxPacker\ItemTooLargeException $e) {
                    $this->_conf->log($e->getMessage());
                }
            }
            /* END Multi package */
        } else {
            $packages[0]['packweight'] = 0;
        }

        $this->_handy->storeId = $storeId;
        $this->_lbl = $this->_handy->setParams($lbl, $prms, $packages);

        $this->_prms = $prms;

        $model = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Items')->getCollection()
            ->addFieldToFilter(['is_store_all', 'store_id'], [
                    ['eq' => 0],
                    [
                        [
                            ['like' => '%,' . $storeId . ',%'],
                            ['like' => '%,' . $storeId],
                            ['like' => $storeId . ',%'],
                            ['like' => $storeId],
                        ]
                    ]
                ]
            )->addFieldToFilter(['negotiated', 'negotiated_amount_from', 'dinamic_price'], [
                    ['eq' => 0],
                    ['lteq' => $orderAmount],
                    ['eq' => 0],
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
            ->addFieldToFilter('country_ids', ['like' => '%' . $request->getDestCountryId() . '%'])
            ->addFieldToFilter('user_group_ids', [['eq' => ''], ['null' => true], ['like' => "%," . $userGroupId . ",%"]]);
        $model->getSelect()->group('upsmethod_id');

        foreach ($model as $method) {
            if ($request->getDestCountryId() && ($request->getDestPostcode() || ($request->getDestRegionCode() && $request->getDestCity()))) {
                if ($method->getCountryIds() != '') {
                    $methodEnd = $this->_getStandardShippingRate($request, $method);
                    if ($methodEnd !== false) {
                        if($methodEnd->getMethod()) {
                            $result->append($methodEnd);
                        } else {
                            return $methodEnd;
                        }
                    }
                }
            }
        }

        if ($this->_coreRegistry->registry('isUpsapGotRates') === null) {
            $this->_coreRegistry->register('isUpsapGotRates', $result);
        }

        return $result;
    }

    protected
    function _getStandardShippingRate(RateRequest $request, $method)
    {
        if (strlen($method->getIsProdAllow()) > 0) {
            foreach ($request->getAllItems() as $item) {
                if (!$item->isDeleted() && !$item->getParentItem()) {
                    $productOrigin = $this->_conf->_objectManager->get('Magento\Catalog\Model\Product')->load($item->getProduct()->getId())->getData();
                    if (isset($productOrigin[$method->getIsProdAllow()]) && $productOrigin[$method->getIsProdAllow()] == 1) {
                        return false;
                    }
                }
            }
        }

        $storeId = $this->_storeManage->getStore()->getId();
        $this->configMethod = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\Upsmethod');

        $rate = $this->_rateMethodFactory->create();
        $rate->setCarrier($this->_code);

        if (strlen($this->_conf->getStoreConfig('carriers/upsap/title', $storeId)) > 0) {
            $rate->setCarrierTitle($this->_conf->getStoreConfig('carriers/upsap/title', $storeId));
        }

        $mTitle = __($method->getName());
        $rate->setMethod($method->getId());
        $ratePrice = false;

        if ($request->getFreeShipping() == true && $method->getFreeShipping() == 1) {
            $ratePrice = 0;
        } else {
            if ($method->getDinamicPrice() == 1) {
                $timezone = new \DateTimeZone($this->_conf->getStoreConfig('general/locale/timezone', $storeId));
                $dateFormat = new \DateTime('now', $timezone);

                if ($method->getTitCloseHour() < $this->timezone->date($dateFormat)->format('H:i')) {
                    $dateFormat->setTimestamp($dateFormat->getTimestamp() + 60 * 60 * 24);
                }

                $weekend = $this->_conf->getStoreConfig('general/locale/weekend', $storeId);
                if (!empty($weekend)) {
                    if (!is_array($weekend)) {
                        $weekend = explode(",", $weekend);
                    }

                    foreach ($weekend as $item) {
                        if (in_array($dateFormat->format("w"), $weekend)) {
                            $dateFormat->setTimestamp($dateFormat->getTimestamp() + 60 * 60 * 24);
                        }
                    }
                }

                $this->_lbl->pickupDate = $dateFormat->format("Ymd");

                if ($this->ratesUpsWithNR === null && $method->getNegotiated() == 1) {
                    $this->ratesUpsWithNR = $this->_handy->getShipRate($this->_lbl, 1);
                }

                if ($this->ratesUpsWithoutNR === null && $method->getNegotiated() == 0) {
                    $this->ratesUpsWithoutNR = $this->_handy->getShipRate($this->_lbl, 0);
                }

                if ($method->getNegotiated() == 1) {
                    $this->ratesUps = $this->ratesUpsWithNR;
                } else {
                    $this->ratesUps = $this->ratesUpsWithoutNR;
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
                        } else {
                            $this->_conf->log("Upsap Error: Price type " . $nameOfPriceType . " does not exist");
                            $this->_conf->log("Upsap Error: Price type ", $this->ratesUps);
                            return false;
                        }

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

                        if ($method->getTimeInTransit() == 1 && isset($ratecode2['day'])) {
                            if ($method->getTitShowFormat() === 'days') {
                                $mTitle .= ' (' . ($ratecode2['day']['days'] + $method->getAddDay()) . __(' day(s)') . ')';
                            } else {
                                $timezone = new \DateTimeZone($this->_conf->getStoreConfig('general/locale/timezone', $storeId));
                                $dateFormat = new DateTime($ratecode2['day']['datetime']['date'], $timezone);
                                if ($method->getTitShowFormat() === 'datetime') {
                                    $mTitle .= ' (' . $dateFormat->format('d') . ' ' . __($dateFormat->format('F')) . ' ' . $dateFormat->format('Y') . ' ' . substr($ratecode2['day']['datetime']['time'], 0, -3) . ')';
                                } else if ($method->getTitShowFormat() === 'full') {
                                    /*$locale = $request->getDestCountryId();*/
                                    $locale = $this->_conf->getStoreConfig('general/locale/code', $storeId);
                                    $mTitle .= ' '. $this->timezone->formatDate($this->timezone->date($dateFormat->getTimestamp(), $locale, true, false), \IntlDateFormatter::FULL);
                                }
                            }
                        }
                    } else {
                        $message = $this->ratesUps;
                        $this->_conf->log('Error carriers params', $this->_prms);
                        $this->_conf->log('Error carriers rates', $message);
                        return false;
                    }
                } else {
                    $message = $this->ratesUps;
                    $this->_conf->log('Error carriers params', $this->_prms);
                    $this->_conf->log('Error carriers rates', $message);
                    return false;
                }

                if ($ratePrice !== false && $method->getAddedValue() != 0 && $method->getAddedValue() != "") {
                    if ($method->getAddedValueType() == 'static') {
                        $ratePrice += (float)str_replace(",", ".", $method->getAddedValue());
                    } else {
                        $ratePrice += ($ratePrice / 100) * str_replace(",", ".", $method->getAddedValue());
                    }
                }
            } else {
                $ratePrice = $method->getPrice();
            }
        }

        $rate->setMethodTitle($mTitle);
        if ($ratePrice > 1 && $this->_conf->getStoreConfig('carriers/upsap/price_format', $storeId) == 1) {
            $ratePrice = ceil($ratePrice * 10) / 10;
        }

        $rate->setPrice(round($ratePrice, $this->_conf->getStoreConfig('carriers/upsap/price_format', $storeId)));
        $rate->setCost(0);
        return $rate;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public
    function getAllowedMethods()
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
        $model = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Items')->getCollection()
            ->addFieldToFilter(['is_store_all', 'store_id'], [
                    ['eq' => 0],
                    [
                        [
                            ['like' => '%,' . $storeId . ',%'],
                            ['like' => '%,' . $storeId],
                            ['like' => $storeId . ',%'],
                            ['like' => $storeId],
                        ]
                    ]
                ]
            )
            ->addFieldToFilter('status', 1);

        foreach ($model as $method) {
            $arrMethods[$method->getId()] = $method->getTitle();
        }

        return $arrMethods;
    }

    protected
    function _getBaseCurrencyKoef($code)
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

    private
    function mappingCurrencyCode($code)
    {
        $currencyMapping = [
            'RMB' => 'CNY',
            'CNH' => 'CNY'
        ];

        return isset($currencyMapping[$code]) ? $currencyMapping[$code] : $code;
    }
}
