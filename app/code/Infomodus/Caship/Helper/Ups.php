<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Caship\Helper;

use DVDoug\BoxPacker\ItemTooLargeException;
use DVDoug\BoxPacker\Packer;
use Infomodus\Caship\Model\Packer\BoxItem;
use Infomodus\Caship\Model\Packer\MyBox;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface as DateTime;

class Ups extends AbstractHelper
{
    public $_context;
    public $_conf;
    public $_registry;
    public $_checkoutSession;
    public $storeId;
    public $negotiated_rates;
    public $rates_tax;
    public $ups;
    public $country;

    /**
     * @var ProductRepository
     */
    protected $product;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Ups constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Infomodus\Caship\Model\Ups $ups
     * @param \Magento\Directory\Model\Country $country
     * @param ProductRepository $product
     * @param DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Infomodus\Caship\Helper\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Infomodus\Caship\Model\Ups $ups,
        \Magento\Directory\Model\Country $country,
        ProductRepository $product,
        DateTime $dateTime
    )
    {
        $this->_registry = $registry;
        parent::__construct($context);
        $this->_context = $context;
        $this->_conf = $config;
        $this->_checkoutSession = $checkoutSession;
        $this->ups = $ups;
        $this->country = $country;
        $this->product = $product;
        $this->dateTime = $dateTime;
    }

    public function getShipRate($lbl, $nr = 0)
    {
        return $lbl->getShipRate($nr);
    }

    public
    function setParams($lbl, $params, $request, $method)
    {
        if ($lbl === null) {
            $lbl = $this->ups;
        }

        $lbl->_handy = $this;

        $lbl->shiptoStateProvinceCode = $this->_conf->escapeXML($params['shiptostateprovincecode']);
        $lbl->shiptoCity = $this->_conf->escapeXML($params['shiptocity']);
        $lbl->shiptoPostalCode = $this->_conf->escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = $this->_conf->escapeXML($params['shiptocountrycode']);
        $lbl->shiptoAddressLine = $this->_conf->escapeXML((!empty($params['shiptoaddressline']) ? $params['shiptoaddressline'] : ""));

        if ($lbl->shiptoCountryCode == 'US' && $lbl->shiptoStateProvinceCode == 'PR') {
            $lbl->shiptoCountryCode = 'PR';
            $lbl->shiptoStateProvinceCode = '';
        }

        if ($lbl->shiptoCountryCode == 'ES') {
            if (in_array(substr($lbl->shiptoPostalCode, 0, 2), ['35', '38'])) {
                $lbl->shiptoCountryCode = 'CI';
                $lbl->shiptoStateProvinceCode = '';
            }
        }

        $packages = [];

        $lbl->insured = 0;

        if ($method->getCompanyType() == 'upsinfomodus') {
            $lbl->AccessLicenseNumber = $this->_conf->getStoreConfig('upslabel/credentials/accesslicensenumber', $this->storeId);
            $lbl->UserID = $this->_conf->getStoreConfig('upslabel/credentials/userid', $this->storeId);
            $lbl->Password = $this->_conf->escapeXML($this->_conf->getStoreConfig('upslabel/credentials/password', $this->storeId));
            $lbl->shipperNumber = $this->_conf->getStoreConfig('upslabel/credentials/shippernumber', $this->storeId);

            $lbl->residentialAddress = 0;

            if ($this->_conf->getStoreConfig('upslabel/shipping/dest_type', $this->storeId) == 3) {
                $lbl->residentialAddress = !empty($params['shiptocompany']) ? 0 : 1;
            } else {
                $lbl->residentialAddress = $this->_conf->getStoreConfig('upslabel/shipping/dest_type', $this->storeId);
            }

            $lbl->insured = $this->_conf->getStoreConfig('upslabel/ratepayment/insured_automaticaly', $this->storeId);

            $params['shipper_no'] = $this->_conf->getStoreConfig('upslabel/shipping/defaultshipper', $this->storeId);
            $address = ObjectManager::getInstance()->get(\Infomodus\Upslabel\Model\Config\Defaultaddress::class)->getAddressesById($params['shipper_no']);

            $lbl->shipperCity = $this->_conf->escapeXML($address->getCity());
            $lbl->shipperStateProvinceCode = $this->_conf->escapeXML($address->getProvinceCode());
            $lbl->shipperPostalCode = $this->_conf->escapeXML($address->getPostalCode());
            $lbl->shipperCountryCode = $this->_conf->escapeXML($address->getCountry());

            $params['shipfrom_no'] = $this->_conf->getStoreConfig('upslabel/shipping/defaultshipfrom', $this->storeId);
            $address = ObjectManager::getInstance()->get(\Infomodus\Upslabel\Model\Config\Defaultaddress::class)->getAddressesById($params['shipfrom_no']);

            $lbl->shipfromCity = $this->_conf->escapeXML($address->getCity());
            $lbl->shipfromStateProvinceCode = $this->_conf->escapeXML($address->getProvinceCode());
            $lbl->shipfromPostalCode = $this->_conf->escapeXML($address->getPostalCode());
            $lbl->shipfromCountryCode = $this->_conf->escapeXML($address->getCountry());
            $lbl->shipfromAddressLine = $this->_conf->escapeXML($address->getStreetOne());

            $lbl->weightUnits = $this->_conf->getStoreConfig('upslabel/weightdimension/weightunits', $this->storeId);
            $lbl->testing = $this->_conf->getStoreConfig('upslabel/testmode/testing', $this->storeId);
            $lbl->unitOfMeasurement = $this->_conf->getStoreConfig('upslabel/weightdimension/unitofmeasurement', $this->storeId);

            if ($this->_conf->getStoreConfig('upslabel/quantum/adult', $this->storeId) != 1 || strpos($this->_conf->getStoreConfig('upslabel/quantum/adult_allow_country', $this->storeId), $lbl->shiptoCountryCode) !== FALSE) {
                $lbl->adult = $this->_conf->escapeXML($this->_conf->getStoreConfig('upslabel/quantum/adult', $this->storeId));
            }

            $packages[0]['weight'] = $request->getPackageWeight();
            $packages[0]['packagingtypecode'] = $this->_conf->getStoreConfig('upslabel/packaging/packagingtypecode', $this->storeId);
            $packages[0]['packweight'] = round($this->_conf->getStoreConfig('upslabel/weightdimension/packweight', $this->storeId), 1) > 0 ? round($this->_conf->getStoreConfig('upslabel/weightdimension/packweight', $this->storeId), 1) : '0';
            $packages[0]['additionalhandling'] = $this->_conf->getStoreConfig('upslabel/ratepayment/additionalhandling', $this->storeId) == 1 ? '<AdditionalHandling />' : '';

            /* Multi package */
            $dimensionSets = ObjectManager::getInstance()->get(\Infomodus\Upslabel\Model\Config\Defaultdimensionsset::class)->toOptionObjects();
            if (count($dimensionSets) > 0 || $this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
                $attributeCodeWidth = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_width', $this->storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_width', $this->storeId) : 'width';
                $attributeCodeHeight = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_height', $this->storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_height', $this->storeId) : 'height';
                $attributeCodeLength = $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_length', $this->storeId) ?
                    $this->_conf->getStoreConfig('upslabel/weightdimension/multipackes_attribute_length', $this->storeId) : 'length';

                try {
                    $countProductInBox = 0;
                    $dimensionsType = $this->_conf->getStoreConfig('upslabel/weightdimension/dimensions_type', $this->storeId);

                    $i = 0;
                    $packer = new Packer;

                    foreach ($request->getAllItems() as $item) {
                        if (!$item->isDeleted() && !$item->getParentItem()) {
                            $itemData = $item->getData();

                            if (!isset($itemData['qty']) && isset($itemData['qty_ordered'])) {
                                $itemData['qty'] = $itemData['qty_ordered'];
                            }

                            $myproduct = $this->product->getById($itemData['product_id'])->getData();

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
                                        if ($this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
                                            $packer->addBox(new MyBox(
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

                                            $packer = new Packer;
                                        } else {
                                            $countProductInBox++;
                                        }
                                    }
                                } else {
                                    $packer->addItem(
                                        new BoxItem(
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

                            if ($countProductInBox == 0 && $this->_conf->getStoreConfig('upslabel/packaging/frontend_multipackes_enable', $this->storeId) == 0) {
                                break;
                            }
                        }
                    }

                    if ($countProductInBox > 0) {
                        foreach ($dimensionSets as $v) {
                            if (!empty($v)) {
                                $packer->addBox(new MyBox(
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
                } catch (ItemTooLargeException $e) {
                    $this->_conf->log($e->getMessage());
                }
            }
            /* END Multi package */
        } else {
            $countryCode = $this->_conf->getStoreConfig('shipping/origin/country_id', $this->storeId);
            $stateCode = $this->_conf->getStoreConfig('shipping/origin/region_id', $this->storeId);
            $states = $this->country->loadByCode($countryCode)->getRegions()->getData();
            if (count($states) > 0) {
                foreach ($states as $state) {
                    if ($state['default_name'] == $stateCode) {
                        $stateCode = $state['code'];
                        break;
                    }

                    if ($state['code'] == $stateCode) {
                        $stateCode = $state['code'];
                        break;
                    }

                    if ($state['region_id'] == $stateCode) {
                        $stateCode = $state['code'];
                        break;
                    }
                }
            }

            $lbl->residentialAddress = $this->_conf->getStoreConfig('carriers/ups/dest_type', $this->storeId) == "RES" ? 1 : 0;

            $lbl->AccessLicenseNumber = $this->_conf->getStoreConfig('carriers/ups/access_license_number', $this->storeId);
            $lbl->UserID = $this->_conf->getStoreConfig('carriers/ups/username', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('carriers/ups/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('carriers/ups/shipper_number', $this->storeId);

            $lbl->shipperCity = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/city', $this->storeId));
            $lbl->shipperStateProvinceCode = $this->_conf->escapeXML($stateCode);
            $lbl->shipperPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/postcode', $this->storeId));
            $lbl->shipperCountryCode = $this->_conf->escapeXML($countryCode);

            $lbl->shipfromStateProvinceCode = $this->_conf->escapeXML($stateCode);
            $lbl->shipfromPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/postcode', $this->storeId));
            $lbl->shipfromCountryCode = $this->_conf->escapeXML($countryCode);

            $lbl->weightUnits = $this->_conf->getStoreConfig('carriers/ups/unit_of_measure', $this->storeId);
            $lbl->testing = $this->_conf->getStoreConfig('carriers/ups/mode_xml', $this->storeId) == 1 ? 0 : 1;

            $packages[0]['weight'] = $request->getPackageWeight();
            $packageTypeCodes = [
                'CP' => '00',
                'ULE' => '01',
                'CSP' => '02',
                'UT' => '03',
                'PAK' => '04',
                'UEB' => '21',
                'UW25' => '24',
                'UW10' => '25',
                'PLT' => '30',
                'SEB' => '2a',
                'MEB' => '2b',
                'LEB' => '2c',];
            $packages[0]['packagingtypecode'] = $packageTypeCodes[$this->_conf->getStoreConfig('carriers/ups/container', $this->storeId)];
            $packages[0]['packweight'] = 0;
            $packages[0]['additionalhandling'] = strlen($this->_conf->getStoreConfig('carriers/ups/handling_fee', $this->storeId)) > 0 && $this->_conf->getStoreConfig('carriers/ups/handling_fee', $this->storeId) > 0 ? '<AdditionalHandling />' : '';
        }

        if ($lbl->shipperCountryCode == 'US' && $lbl->shipperStateProvinceCode == 'PR') {
            $lbl->shipperCountryCode = 'PR';
            $lbl->shipperStateProvinceCode = '';
        }

        if ($lbl->shipperCountryCode == 'ES') {
            if (in_array(substr($lbl->shipperPostalCode, 0, 2), ['35', '38'])) {
                $lbl->shipperCountryCode = 'CI';
                $lbl->shipperStateProvinceCode = '';
            }
        }

        if ($lbl->shipfromCountryCode == 'US' && $lbl->shipfromStateProvinceCode == 'PR') {
            $lbl->shipfromCountryCode = 'PR';
            $lbl->shipfromStateProvinceCode = '';
        }

        if ($lbl->shipfromCountryCode == 'ES') {
            if (in_array(substr($lbl->shipfromPostalCode, 0, 2), ['35', '38'])) {
                $lbl->shipfromCountryCode = 'CI';
                $lbl->shipfromStateProvinceCode = '';
            }
        }

        $lbl->packages = $packages;

        $timezone = new \DateTimeZone($this->_conf->getStoreConfig('general/locale/timezone', $this->storeId));
        $dateFormat = new \DateTime('now', $timezone);

        if ($method->getTitCloseHour() < $this->dateTime->date($dateFormat)->format('H:i')) {
            $dateFormat->setTimestamp($dateFormat->getTimestamp() + 60 * 60 * 24);
        }

        $weekend = $this->_conf->getStoreConfig('general/locale/weekend', $this->storeId);
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

        $lbl->pickupDate = $dateFormat->format("Ymd");

        return $lbl;
    }
}