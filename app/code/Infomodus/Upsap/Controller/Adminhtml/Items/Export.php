<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Items;

class Export extends \Infomodus\Upsap\Controller\Adminhtml\Items
{
    protected $_conf;
    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Infomodus\Upsap\Helper\Config $conf
    )
    {
        $this->_conf = $conf;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory);
    }

    public function execute()
    {
        $fileName = 'ups_access_point_methods.csv';
        ob_start();
        $fp = fopen( 'php://output', 'w');
        $collection = $this->_conf->_objectManager->create('Infomodus\Upsap\Model\Items')->getCollection();
        $firstRow = array(
            'title',
            'name',
            'ups_method_code',
            'country_ids',
            'zip_min',
            'zip_max',
            'weight_min',
            'weight_max',
            'qty_min',
            'qty_max',
            'order_amount_min',
            'order_amount_max',
            'dinamic_price',
            'price',
            'status',
            'added_value_type',
            'added_value',
            'negotiated',
            'negotiated_amount_from',
            'tax',
            'store_id',
            'time_in_transit',
            'add_day',
            'free_shipping',
            'user_group_ids',
        );
        fputcsv($fp, $firstRow, ',');
        foreach($collection AS $item){
            $itemData = $item->getData();
            $row = array();
            $row[] = $itemData['title'];
            $row[] = $itemData['name'];
            $row[] = $itemData['upsmethod_id'];
            $row[] = $itemData['country_ids'];
            $row[] = $itemData['zip_min'];
            $row[] = $itemData['zip_max'];
            $row[] = $itemData['weight_min'];
            $row[] = $itemData['weight_max'];
            $row[] = $itemData['qty_min'];
            $row[] = $itemData['qty_max'];
            $row[] = $itemData['amount_min'];
            $row[] = $itemData['amount_max'];
            $row[] = $itemData['dinamic_price'];
            $row[] = $itemData['price'];
            $row[] = $itemData['status'];
            $row[] = $itemData['added_value_type'];
            $row[] = $itemData['added_value'];
            $row[] = $itemData['negotiated'];
            $row[] = $itemData['negotiated_amount_from'];
            $row[] = $itemData['tax'];
            if ($itemData['is_store_all'] == 0) {
                $row[] = 'all';
            } else {
                $row[] = $itemData['store_id'];
            }
            $row[] = $itemData['time_in_transit'];
            $row[] = $itemData['add_day'];
            $row[] = $itemData['free_shipping'];
            $row[] = trim($itemData['user_group_ids'], ',');
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
