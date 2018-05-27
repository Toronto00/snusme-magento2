<?php

namespace Vconnect\Allinone\Model\ResourceModel;

class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('vconnect_allinone_quote_shipping_rate', 'vconnect_allinone_quote_shipping_rate_id');
    }
}