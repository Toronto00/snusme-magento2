<?php
namespace Infomodus\Upsap\Model\Config;

class PriceFormat implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'xx.yy', 'value' => 2],
            ['label' => 'xx.y0', 'value' => 1],
        ];
    }
}