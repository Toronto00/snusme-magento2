<?php

namespace Vconnect\Allinone\Model\ResourceModel\Rate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(
            'Vconnect\Allinone\Model\Rate',
            'Vconnect\Allinone\Model\ResourceModel\Rate'
        );
    }
}