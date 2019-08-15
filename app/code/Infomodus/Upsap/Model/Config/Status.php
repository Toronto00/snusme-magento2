<?php
namespace Infomodus\Upsap\Model\Config;

class Status extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Enabled'), 'value' => 1],
            ['label' => __('Disabled'), 'value' => 0],
        ];
        return $c;
    }
}