<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml\Items;

class Export extends \Infomodus\Caship\Controller\Adminhtml\Items
{
    public function execute()
    {
        $fileName = 'infomodus_shipping_methods.csv';
        ob_start();
        $fp = fopen( 'php://output', 'w');
        $firstRow = array(
            'title',
            'name',
            'dynamic_price',
            'price',
            'carrier_code',
            'carrier_method_code',
            'added_value_type',
            'added_value',
            'negotiated',
            'negotiated_amount_from',
            'tax',
            'rural',
            'time_in_transit',
            'tit_show_format',
            'tit_close_hour',
            'add_day',
            'order_amount_min',
            'order_amount_max',
            'weight_min',
            'weight_max',
            'qty_min',
            'qty_max',
            'zip_min',
            'zip_max',
            'country_ids',
            'store_code',
            'status',
            'free_shipping',
            'user_group_ids',
            'is_prod_allow',
        );
        fputcsv($fp, $firstRow, ',');
        foreach($this->methods->load() AS $item){
            $itemData = $item->getData();
            $row = [];
            $row[] = $itemData['title'];
            $row[] = $itemData['name'];
            $row[] = $itemData['dinamic_price'];
            $row[] = $itemData['price'];
            $row[] = $itemData['company_type'];
            if($itemData['company_type'] == 'ups'|| $itemData['company_type'] == 'upsinfomodus') {
                $row[] = $itemData['upsmethod_id'];
            } else if($itemData['company_type'] == 'dhl'|| $itemData['company_type'] == 'dhlinfomodus') {
                $row[] = $itemData['dhlmethod_id'];
            } else if($itemData['company_type'] == 'fedex'|| $itemData['company_type'] == 'fedexinfomodus') {
                $row[] = $itemData['fedexmethod_id'];
            } else {
                $row[] = '';
            }
            $row[] = $itemData['added_value_type'];
            $row[] = $itemData['added_value'];
            $row[] = $itemData['negotiated'];
            $row[] = $itemData['negotiated_amount_from'];
            $row[] = $itemData['tax'];
            $row[] = $itemData['rural'];
            $row[] = $itemData['time_in_transit'];
            $row[] = $itemData['tit_show_format'];
            $row[] = $itemData['tit_close_hour'];
            $row[] = $itemData['add_day'];
            $row[] = $itemData['amount_min'];
            $row[] = $itemData['amount_max'];
            $row[] = $itemData['weight_min'];
            $row[] = $itemData['weight_max'];
            $row[] = $itemData['qty_min'];
            $row[] = $itemData['qty_max'];
            $row[] = $itemData['zip_min'];
            $row[] = $itemData['zip_max'];
            if($itemData['is_country_all']==0) {
                $row[] = 'all';
            } else {
                $row[] = $itemData['country_ids'];
            }

            if ($itemData['is_store_all'] == 0) {
                $row[] = 'all';
            } else {
                $stores = explode(",", $itemData['store_id']);
                $storeCodes = [];
                foreach ($stores as $v) {
                    $storeModel = $this->storeManagerInterface->getStore(trim($v));
                    if($storeModel) {
                        $storeCodes[] = $storeModel->getCode();
                    }
                }
                $row[] = implode(",", $storeCodes);
            }
            $row[] = $itemData['status'];
            $row[] = $itemData['free_shipping'];
            $row[] = trim($itemData['user_group_ids'], ',');
            $row[] = trim($itemData['is_prod_allow'], ',');
            fputcsv($fp, $row, ',');
        }
        fclose($fp);
        $file_content = ob_get_contents();
        ob_end_clean();
        return $this->fileFactory->create(
            $fileName,
            $file_content,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'text/csv'
        );
    }
}
