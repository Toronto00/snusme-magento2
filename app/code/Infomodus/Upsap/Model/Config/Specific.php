<?php
namespace Infomodus\Upsap\Model\Config;

class Specific implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('All'), 'value' => '0'],
            ['label' => __('Specific'), 'value' => '1'],
        ];
    }
}