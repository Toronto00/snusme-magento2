<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Caship\Helper;

use DVDoug\BoxPacker\ItemTooLargeException;
use DVDoug\BoxPacker\Packer;
use Infomodus\Caship\Model\Packer\MyBox;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;

class Dhl extends AbstractHelper
{
    public $_context;
    public $_conf;
    public $_registry;
    public $storeId;
    public $error;

    public $rates_tax;
    protected $dhl;

    /**
     * @var ProductRepository
     */
    protected $product;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Infomodus\Caship\Helper\Config $config,
        \Magento\Framework\Registry $registry,
        \Infomodus\Caship\Model\Dhl $dhl,
        ProductRepository $product
    )
    {
        $this->_registry = $registry;
        parent::__construct($context);
        $this->_context = $context;
        $this->_conf = $config;
        $this->dhl = $dhl;
        $this->product = $product;
    }


    public function getShipRate($lbl, $isDutiable = false, $declaredValue = 0, $currencyCode = "")
    {
        $declared_value = explode('.', (string)round($declaredValue, 2));
        if (count($declared_value) > 1 && strlen($declared_value[1]) == 1) {
            $declaredValue = round($declaredValue, 2) . '0';
        } else {
            $declaredValue = round($declaredValue, 2);
        }

        $lbl->declaredValue = $declaredValue;
        $lbl->currencyCode = $currencyCode;
        return $lbl->getShipPrice($isDutiable);
    }

    public
    function setParams($lbl, $params, $request, $method)
    {
        if ($lbl === NULL) {
            $lbl = $this->dhl;
        }

        $lbl->_handy = $this;

        $lbl->shiptoCity = $this->_conf->escapeXML($params['shiptocity']);
        $shiptoStateProvinceCode = $this->_conf->escapeXML($params['stateprovincecode']);
        $lbl->shiptoPostalCode = $this->_conf->escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = $this->_conf->escapeXML($params['shiptocountrycode']);

        if($lbl->shiptoCountryCode == 'US' && $shiptoStateProvinceCode == 'PR'){
            $lbl->shiptoCountryCode = 'PR';
        }

        if($lbl->shiptoCountryCode == 'BL'){
            $lbl->shiptoCountryCode = 'XY';
        }

        $packages = [];
        $packages[0]['weight'] = $request->getPackageWeight();

        if ($method->getCompanyType() == 'dhlinfomodus') {
            $lbl->UserID = $this->_conf->getStoreConfig('dhllabel/credentials/userid', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('dhllabel/credentials/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $this->storeId);

            $params['shipper_no'] = $this->_conf->getStoreConfig('dhllabel/shipping/defaultshipper', $this->storeId);
            $lbl->shipperCity = $this->_conf->escapeXML($this->_conf->getStoreConfig('dhllabel/address_' . $params['shipper_no'] . '/city', $this->storeId));
            $shipperStateProvinceCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('dhllabel/address_' . $params['shipper_no'] . '/stateprovincecode', $this->storeId));
            $lbl->shipperPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('dhllabel/address_' . $params['shipper_no'] . '/postalcode', $this->storeId));
            $lbl->shipperCountryCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('dhllabel/address_' . $params['shipper_no'] . '/countrycode', $this->storeId));

            if($lbl->shipperCountryCode == 'US' && $shipperStateProvinceCode == 'PR'){
                $lbl->shipperCountryCode = 'PR';
            }

            if($lbl->shipperCountryCode == 'BL'){
                $lbl->shipperCountryCode = 'XY';
            }

            $lbl->currencyCode = $this->_conf->getStoreConfig('dhllabel/ratepayment/currencycode', $this->storeId);
            $lbl->testing = $this->_conf->getStoreConfig('dhllabel/testmode/testing', $this->storeId);
            $lbl->insured_automaticaly = $this->_conf->getStoreConfig('dhllabel/ratepayment/insured', $this->storeId);

            $packages[0]['packweight'] = round($this->_conf->getStoreConfig('dhllabel/weightdimension/packweight', $this->storeId), 1) > 0 ? round($this->_conf->getStoreConfig('dhllabel/weightdimension/packweight', $this->storeId), 1) : '0';

            /* Multi package */
            $dimensionSets = ObjectManager::getInstance()->get(\Infomodus\Dhllabel\Model\Config\Defaultdimensionsset::class)->toOptionArray($this->storeId);
            if (count($dimensionSets) > 0 || $this->_conf->getStoreConfig('dhllabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
                $attributeCodeWidth = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_width', $this->storeId) ?
                    $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_width', $this->storeId) : 'width';
                $attributeCodeHeight = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_height', $this->storeId) ?
                    $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_height', $this->storeId) : 'height';
                $attributeCodeLength = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_length', $this->storeId) ?
                    $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_length', $this->storeId) : 'length';

                try {
                    $countProductInBox = 0;
                    $packer = new Packer;
                    $i = 0;

                    foreach ($request->getAllItems() as $item) {
                        if (!$item->isDeleted() && !$item->getParentItem()) {
                            $itemData = $item->getData();
                            if (!isset($itemData['qty']) && isset($itemData['qty_ordered'])) {
                                $itemData['qty'] = $itemData['qty_ordered'];
                            }

                            $myproduct = $this->product->getById($itemData['product_id'])->getData();

                            $myproduct['weight'] = $item->getWeight();

                            for ($ik = 0; $ik < $itemData['qty']; $ik++) {
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
                                    if ($this->_conf->getStoreConfig('dhllabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
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
                            }

                            if ($countProductInBox == 0 && $this->_conf->getStoreConfig('dhllabel/packaging/frontend_multipackes_enable', $this->storeId) == 0) {
                                break;
                            }
                        }
                    }

                    if ($countProductInBox > 0) {
                        foreach ($dimensionSets as $v) {
                            if ($v['value'] !== 0) {
                                $packer->addBox(new MyBox(
                                        $v['value'],
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/outer_width', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/outer_length', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/outer_height', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/emptyWeight', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/width', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/length', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/height', $this->storeId),
                                        $this->_conf->getStoreConfig('dhllabel/dimansion_' . $v['value'] . '/maxWeight', $this->storeId)
                                    )
                                );
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
            $lbl->UserID = $this->_conf->getStoreConfig('carriers/dhl/id', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('carriers/dhl/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('carriers/dhl/account', $this->storeId);

            $lbl->shipperCity = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/city', $this->storeId));
            $shipperStateProvinceCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/region_id', $this->storeId));
            $lbl->shipperPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/postcode', $this->storeId));
            $lbl->shipperCountryCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/country_id', $this->storeId));

            if($lbl->shipperCountryCode == 'US' && $shipperStateProvinceCode == 'PR'){
                $lbl->shipperCountryCode = 'PR';
            }

            if($lbl->shipperCountryCode == 'BL'){
                $lbl->shipperCountryCode = 'XY';
            }

            $lbl->testing = ('https://xmlpi-ea.dhl.com/XMLShippingServlet' == $this->_conf->getStoreConfig('carriers/dhl/gateway_url', $this->storeId) ? 0 : 1);
            $lbl->currencyCode = $this->_conf->getStoreConfig('currency/options/base', $this->storeId);

            $packages[0]['packweight'] = 0;
        }

        $declared_value = explode('.', (string)round($params['declared_value'], 2));
        if (count($declared_value) > 1 && strlen($declared_value[1]) == 1) {
            $declared_value = round($params['declared_value'], 2) . '0';
        } else {
            $declared_value = round($params['declared_value'], 2);
        }

        $lbl->declaredValue = $declared_value;
        $lbl->packages = $packages;
        return $lbl;
    }
}