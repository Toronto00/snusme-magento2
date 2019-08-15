<?php

namespace Infomodus\Upsap\Model\Config;

class AddedValueType extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['value' => 'static', 'label' => 'Amount'],
            ['value' => 'percent', 'label' => 'Percent']
        ];
        return $c;
    }
}