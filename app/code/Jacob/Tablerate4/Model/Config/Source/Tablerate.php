<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jacob\Tablerate4\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Tablerate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    protected $_carrierTablerate;

    /**
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate
     */
    public function __construct(\Jacob\Tablerate4\Model\Carrier\Tablerate $carrierTablerate)
    {
        $this->_carrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
