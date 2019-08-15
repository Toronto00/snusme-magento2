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
use Infomodus\Fedexlabel\Model\Config\Defaultdimensionsset;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ObjectManager;

class Fedex extends AbstractHelper
{
    public $_context;
    public $_conf;
    public $_registry;
    public $storeId;
    public $error;

    public $rates_tax;
    protected $fedexModel;
    protected $product;

    public function __construct(
        Context $context,
        Config $config,
        Registry $registry,
        \Infomodus\Caship\Model\Fedex $fedexModel,
        ProductRepository $product
    )
    {
        $this->_registry = $registry;
        parent::__construct($context);
        $this->_context = $context;
        $this->_conf = $config;
        $this->fedexModel = $fedexModel;
        $this->product = $product;
    }


    public function getShipRate($lbl)
    {
        return $lbl->getShipPrice();
    }

    public
    function setParams($lbl, $params, $request, $method)
    {
        if ($lbl === NULL) {
            $lbl = $this->fedexModel;
        }

        $packages = [];
        $packages[0]['weight'] = $request->getPackageWeight();

        if ($method->getCompanyType() == 'fedexinfomodus') {
            $packages[0]['packweight'] = round($this->_conf->getStoreConfig('fedexlabel/weightdimension/packweight', $this->storeId), 1) > 0 ? round($this->_conf->getStoreConfig('fedexlabel/weightdimension/packweight', $this->storeId), 1) : '0';

            /* Multi package */
            $dimensionSets = ObjectManager::getInstance()->get(Defaultdimensionsset::class)->toOptionArray($this->storeId);
            if (count($dimensionSets) > 0 || $this->_conf->getStoreConfig('fedexlabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
                if ($this->_conf->getStoreConfig('fedexlabel/weightdimension/dimensions_type', $this->storeId) == 0) {
                    $attributeCodeWidth = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_width', $this->storeId) ?
                        $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_width', $this->storeId) : 'width';
                    $attributeCodeHeight = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_height', $this->storeId) ?
                        $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_height', $this->storeId) : 'height';
                    $attributeCodeLength = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_length', $this->storeId) ?
                        $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_length', $this->storeId) : 'length';
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
                                    if ($this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)
                                        && isset($myproduct[$this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)])
                                        && $myproduct[$this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)] == 1
                                        && $this->_conf->getStoreConfig('fedexlabel/packaging/frontend_multipackes_enable', $this->storeId) != 1) {
                                        $packerWithoutBox = new Packer();
                                        $myproduct = $this->_conf->getProductSizes(
                                            $item,
                                            $myproduct,
                                            $packerWithoutBox,
                                            $attributeCodeWidth,
                                            $attributeCodeHeight,
                                            $attributeCodeLength
                                        );
                                        $packerWithoutBox->addBox(new MyBox(
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
                                        $packedBoxes = $packerWithoutBox->pack();
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
                                        $countProductInBox++;
                                        continue;
                                    } else {
                                        $myproduct = $this->_conf->getProductSizes(
                                            $item,
                                            $myproduct,
                                            $packer,
                                            $attributeCodeWidth,
                                            $attributeCodeHeight,
                                            $attributeCodeLength
                                        );
                                    }

                                    if ($myproduct === false) {
                                        $countProductInBox = 0;
                                        break;
                                    } else {
                                        if ($this->_conf->getStoreConfig('fedexlabel/packaging/frontend_multipackes_enable', $this->storeId) == 1) {
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

                                if ($countProductInBox == 0 && $this->_conf->getStoreConfig('fedexlabel/packaging/frontend_multipackes_enable', $this->storeId) == 0) {
                                    break;
                                }
                            }
                        }

                        if ($countProductInBox > 0) {
                            foreach ($dimensionSets as $v) {
                                if ($v['value'] !== 0) {
                                    $packer->addBox(new MyBox(
                                            $v['value'],
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/outer_width', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/outer_length', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/outer_height', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/emptyWeight', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/width', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/length', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/height', $this->storeId),
                                            $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $v['value'] . '/maxWeight', $this->storeId)
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
                } else {
                    $defaultBox = $this->_conf->getStoreConfig('fedexlabel/weightdimension/default_dimensions_box', $this->storeId);
                    if (!empty($defaultBox)) {
                        $packages[0]['width'] = $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $defaultBox . '/outer_width', $this->storeId);
                        $packages[0]['length'] = $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $defaultBox . '/outer_length', $this->storeId);
                        $packages[0]['height'] = $this->_conf->getStoreConfig('fedexlabel/dimansion_' . $defaultBox . '/outer_height', $this->storeId);
                    }
                }
            }
            /* END Multi package */
        } else {
            $packages[0]['packweight'] = 0;
        }

        $lbl->_handy = $this;
        $lbl->packages = $packages;

        $lbl->shiptoCity = $this->_conf->escapeXML($params['shiptocity']);
        $lbl->shiptoPostalCode = $this->_conf->escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = $this->_conf->escapeXML($params['shiptocountrycode']);

        if ($method->getCompanyType() == 'fedexinfomodus') {
            $lbl->UserID = $this->_conf->getStoreConfig('fedexlabel/credentials/userid', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('fedexlabel/credentials/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/shippernumber', $this->storeId);
            $lbl->meterNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/meter_number', $this->storeId);

            $params['shipper_no'] = $this->_conf->getStoreConfig('fedexlabel/shipping/defaultshipper', $this->storeId);
            $lbl->shipperCity = $this->_conf->escapeXML($this->_conf->getStoreConfig('fedexlabel/address_' . $params['shipper_no'] . '/city', $this->storeId));
            $lbl->shipperPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('fedexlabel/address_' . $params['shipper_no'] . '/postalcode', $this->storeId));
            $lbl->shipperCountryCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('fedexlabel/address_' . $params['shipper_no'] . '/countrycode', $this->storeId));

            $lbl->currencyCode = $this->_conf->getStoreConfig('fedexlabel/ratepayment/currencycode', $this->storeId);
            $lbl->testing = $this->_conf->getStoreConfig('fedexlabel/testmode/testing', $this->storeId);
            $lbl->weightUnits = $this->_conf->getStoreConfig('fedexlabel/weightdimension/weightunits', $this->storeId);
            $lbl->unitOfMeasurement = $this->_conf->getStoreConfig('fedexlabel/weightdimension/unitofmeasurement', $this->storeId);
            $lbl->insured_automaticaly = $this->_conf->getStoreConfig('fedexlabel/ratepayment/insured_automaticaly', $this->storeId);
        } else {
            $lbl->UserID = $this->_conf->getStoreConfig('carriers/fedex/key', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('carriers/fedex/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('carriers/fedex/account', $this->storeId);
            $lbl->meterNumber = $this->_conf->getStoreConfig('carriers/fedex/meter_number', $this->storeId);

            $lbl->UserID = $this->_conf->getStoreConfig('carriers/fedex/id', $this->storeId);
            $lbl->Password = $this->_conf->getStoreConfig('carriers/fedex/password', $this->storeId);
            $lbl->shipperNumber = $this->_conf->getStoreConfig('carriers/fedex/account', $this->storeId);

            $lbl->shipperCity = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/city', $this->storeId));
            $lbl->shipperPostalCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/postcode', $this->storeId));
            $lbl->shipperCountryCode = $this->_conf->escapeXML($this->_conf->getStoreConfig('shipping/origin/country_id', $this->storeId));

            $lbl->currencyCode = $this->_conf->getStoreConfig('currency/options/base', $this->storeId);
            $lbl->testing = $this->_conf->getStoreConfig('carriers/fedex/sandbox_mode', $this->storeId);
            $lbl->weightUnits = $this->_conf->getStoreConfig('carriers/fedex/unit_of_measure', $this->storeId);
        }

        $declared_value = explode('.', (string)round($params['declared_value'], 2));
        if (count($declared_value) > 1 && strlen($declared_value[1]) == 1) {
            $declared_value = round($params['declared_value'], 2) . '0';
        } else {
            $declared_value = round($params['declared_value'], 2);
        }

        $lbl->codMonetaryValue = $declared_value;
        return $lbl;
    }
}