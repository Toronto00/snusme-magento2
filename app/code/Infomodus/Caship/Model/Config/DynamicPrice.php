<?php
namespace Infomodus\Caship\Model\Config;

class DynamicPrice implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('Static'), 'value' => 0],
            ['label' => __('Dynamic'), 'value' => 1],
        ];
    }
}