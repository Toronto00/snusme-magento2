<?php

namespace Infomodus\Caship\Model\Config;

use Magento\Framework\Filesystem;

class Import extends \Magento\Config\Model\Config\Backend\File
{
    protected $_conf;
    protected $storeRepository;
    protected $methods;
    protected $method;
    protected $methodsCollection;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Infomodus\Caship\Helper\Config $conf,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Infomodus\Caship\Model\ItemsFactory $methods,
        \Infomodus\Caship\Model\Items $method,
        \Infomodus\Caship\Model\ResourceModel\Items\Collection $methodsCollection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_conf = $conf;
        $this->storeRepository = $storeRepository;
        $this->methods = $methods;
        $this->method = $method;
        $this->methodsCollection = $methodsCollection;
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        parent::beforeSave();
        $value = $this->getValue();
        $file = $this->_getUploadDir() . '/' . $value;
        if ($value) {
            try {
                $csvLines = file($file);
                $delimiter = ",";
                if (strpos($csvLines[0], ';') !== false) {
                    $delimiter = ";";
                }

                $head = str_getcsv($csvLines[0], $delimiter);
                unset($csvLines[0]);

                foreach ($csvLines as $row) {
                    $csv = str_getcsv($row, $delimiter);
                    $model = $this->methods->create();
                    $items = $this->methodsCollection;
                    foreach ($csv as $key => $col) {
                        $colData = trim($col);
                        switch (trim($head[$key])) {
                            case 'title':
                                $items->addFieldToFilter('title', $colData);
                                if (count($items) > 0) {
                                    $model = $this->method->load($items->load()->getFirstItem()->getId());
                                } else {
                                    $model->setTitle($colData);
                                }

                                break;
                            case 'name':
                                $model->setName($colData);
                                $items->addFieldToFilter('name', $colData);
                                break;
                            case 'dynamic_price':
                                $model->setDinamicPrice($colData);
                                $items->addFieldToFilter('dinamic_price', $colData);
                                break;
                            case 'price':
                                $model->setPrice($colData);
                                $items->addFieldToFilter('price', $colData);
                                break;
                            case 'carrier_code':
                                $model->setCompanyType(strtolower($colData));
                                $items->addFieldToFilter('company_type', strtolower($colData));
                                break;
                            case 'carrier_method_code':
                                $carrier_code = strtolower($csv[array_search('carrier_code', $head)]);
                                if ($carrier_code == 'ups' || $carrier_code == 'upsinfomodus') {
                                    if (strlen($colData) == 1) {
                                        $colData = "0" . $colData;
                                    }
                                    $model->setUpsmethodId($colData);
                                    $items->addFieldToFilter('upsmethod_id', $colData);
                                } else if ($carrier_code == 'dhl' || $carrier_code == 'dhlinfomodus') {
                                    $model->setDhlmethodId($colData);
                                    $items->addFieldToFilter('dhlmethod_id', $colData);
                                } else if ($carrier_code == 'fedex' || $carrier_code == 'fedexinfomodus') {
                                    $model->setFedexmethodId($colData);
                                    $items->addFieldToFilter('fedexmethod_id', $colData);
                                }

                                break;
                            case 'store_code':
                                if (strtolower($colData) != 'all') {
                                    $storeCodes = explode(",", $colData);
                                    $storeIds = array();
                                    foreach ($storeCodes as $v){
                                        $storeCodeModel = $this->storeRepository->get($v);
                                        if($storeCodeModel) {
                                            $storeIds[] = $storeCodeModel->getId();
                                        }
                                    }

                                    $storeIdsString = implode(",", $storeIds);
                                    $model->setStoreId($storeIdsString);
                                    $items->addFieldToFilter('store_id', $storeIdsString);
                                    $model->setIsStoreAll(1);
                                } else {
                                    $model->setIsStoreAll(0);
                                }

                                break;
                            case 'country_ids':
                                if (strtolower($colData) != 'all') {
                                    $model->setCountryIds(str_replace(" ", "", $colData));
                                    $items->addFieldToFilter('country_ids', str_replace(" ", "", $colData));
                                    $model->setIsCountryAll(1);
                                    $items->addFieldToFilter('is_country_all', 1);
                                } else {
                                    $model->setIsCountryAll(0);
                                    $items->addFieldToFilter('is_country_all', 0);
                                }

                                break;
                            case 'status':
                                $model->setStatus($colData);
                                $items->addFieldToFilter('status', $colData);
                                break;
                            case 'order_amount_min':
                                $model->setAmountMin(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('amount_min', str_replace(',', '.', $colData));
                                break;
                            case 'order_amount_max':
                                $model->setAmountMax(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('amount_max', str_replace(',', '.', $colData));
                                break;
                            case 'negotiated':
                                $model->setNegotiated($colData);
                                $items->addFieldToFilter('negotiated', $colData);
                                break;
                            case 'negotiated_amount_from':
                                $model->setNegotiatedFmountFrom(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('negotiated_amount_from', str_replace(',', '.', $colData));
                                break;
                            case 'tax':
                                $model->setTax($colData);
                                $items->addFieldToFilter('tax', $colData);
                                break;
                            case 'weight_min':
                                $model->setWeightMin(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('weight_min', str_replace(',', '.', $colData));
                                break;
                            case 'weight_max':
                                $model->setWeightMax(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('weight_max', str_replace(',', '.', $colData));
                                break;
                            case 'qty_min':
                                $model->setQtyMin(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('qty_min', str_replace(',', '.', $colData));
                                break;
                            case 'qty_max':
                                $model->setQtyMax(str_replace(',', '.', $colData));
                                $items->addFieldToFilter('qty_max', str_replace(',', '.', $colData));
                                break;
                            case 'zip_min':
                                $model->setZipMin($colData);
                                $items->addFieldToFilter('zip_min', $colData);
                                break;
                            case 'zip_max':
                                $model->setZipMax($colData);
                                $items->addFieldToFilter('zip_max', $colData);
                                break;
                            case 'time_in_transit':
                                $model->setTimeInTransit($colData);
                                $items->addFieldToFilter('time_in_transit', $colData);
                                break;
                            case 'add_day':
                                $model->setAddDay($colData);
                                $items->addFieldToFilter('add_day', $colData);
                                break;
                            case 'tit_close_hour':
                                $model->setTitCloseHour($colData);
                                $items->addFieldToFilter('tit_close_hour', $colData);
                                break;
                            case 'tit_show_format':
                                $model->setTitShowFormat($colData);
                                $items->addFieldToFilter('tit_show_format', $colData);
                                break;
                            case 'added_value_type':
                                $model->setAddedValueType($colData);
                                $items->addFieldToFilter('added_value_type', $colData);
                                break;
                            case 'added_value':
                                $model->setAddedValue($colData);
                                $items->addFieldToFilter('added_value', $colData);
                                break;
                            case 'free_shipping':
                                $model->setFreeShipping($colData);
                                $items->addFieldToFilter('free_shipping', $colData);
                                break;
                            case 'user_group_ids':
                                if(strlen(trim($colData, ',')) > 0) {
                                    $model->setUserGroupIds(',' . trim($colData, ',') . ',');
                                } else {
                                    $model->setUserGroupIds('');
                                }

                                $items->addFieldToFilter('user_group_ids', $colData);
                                break;
                            case 'is_prod_allow':
                                if(strlen(trim($colData, ',')) > 0) {
                                    $model->setIsProdAllow(',' . trim($colData, ',') . ',');
                                } else {
                                    $model->setIsProdAllow('');
                                }

                                $items->addFieldToFilter('is_prod_allow', $colData);
                                break;
                            case 'rural':
                                $model->setRural($colData);
                                $items->addFieldToFilter('rural', $colData);
                                break;
                        }
                    }

                    if (count($items->load()) == 0) {
                        $model->save();
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_conf->log($e->getMessage());
                return $this;
            }
        }

        return $this;
    }

    protected function _getAllowedExtensions()
    {
        return ['csv'];
    }
}