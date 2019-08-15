<?php
namespace Infomodus\Upsap\Model\Config;

use Magento\Framework\Filesystem;

class Import extends \Magento\Config\Model\Config\Backend\File
{
    protected $_conf;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Infomodus\Upsap\Helper\Config $conf,
        array $data = []
    )
    {
        $this->_conf = $conf;
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
                if (strpos($csvLines[0], ';') !== FALSE) {
                    $delimiter = ";";
                }
                $head = str_getcsv($csvLines[0], $delimiter);
                unset($csvLines[0]);

                foreach ($csvLines AS $row) {
                    $csv = str_getcsv($row, $delimiter);
                    $model = $this->_conf->_objectManager->create('Infomodus\Upsap\Model\Items');
                    $items = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Items')->getCollection();
                    foreach ($csv AS $key => $col) {
                        $colData = trim($col);
                        switch (trim($head[$key])) {
                            case 'title':
                                $items->addFieldToFilter('title', $colData);
                                if (count($items) > 0) {
                                    $model = $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Items')->load($items->getFirstItem()->getId());
                                } else {
                                    $model->setTitle($colData);
                                }
                                break;
                            case 'name':
                                $model->setName($colData);
                                break;
                            case 'ups_method_code':
                                if (strlen($colData) == 1) {
                                    $colData = "0" . $colData;
                                }
                                $model->setUpsmethodId($colData);
                                break;
                            case 'store_id':
                                if (strtolower($colData) != 'all') {
                                    $model->setStoreId($colData);
                                    $items->addFieldToFilter('store_id', $colData);
                                    $model->setIsStoreAll(1);
                                } else {
                                    $model->setIsStoreAll(0);
                                }

                                break;
                            case 'country_ids':
                                $model->setCountryIds(str_replace(" ", "", $colData));
                                break;
                            case 'price':
                                $model->setPrice($colData);
                                break;
                            case 'status':
                                $model->setStatus($colData);
                                break;
                            case 'dinamic_price':
                                $model->setDinamicPrice($colData);
                                break;
                            case 'order_amount_min':
                                $model->setAmountMin(str_replace(',', '.', $colData));
                                break;
                            case 'order_amount_max':
                                $model->setAmountMax(str_replace(',', '.', $colData));
                                break;
                            case 'negotiated':
                                $model->setNegotiated($colData);
                                break;
                            case 'negotiated_amount_from':
                                $model->setNegotiatedFmountFrom(str_replace(',', '.', $colData));
                                break;
                            case 'tax':
                                $model->setTax($colData);
                                break;
                            case 'weight_min':
                                $model->setWeightMin(str_replace(',', '.', $colData));
                                break;
                            case 'weight_max':
                                $model->setWeightMax(str_replace(',', '.', $colData));
                                break;
                            case 'qty_min':
                                $model->setQtyMin(str_replace(',', '.', $colData));
                                break;
                            case 'qty_max':
                                $model->setQtyMax(str_replace(',', '.', $colData));
                                break;
                            case 'zip_min':
                                $model->setZipMin($colData);
                                break;
                            case 'zip_max':
                                $model->setZipMax($colData);
                                break;
                            case 'time_in_transit':
                                $model->setTimeInTransit($colData);
                                $items->addFieldToFilter('time_in_transit', $colData);
                                break;
                            case 'add_day':
                                $model->setAddDay($colData);
                                $items->addFieldToFilter('add_day', $colData);
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

                                $items->addFieldToFilter('free_shipping', $colData);
                                break;
                        }
                    }
                    $model->save();
                }
            } catch (\Exception $e) {
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