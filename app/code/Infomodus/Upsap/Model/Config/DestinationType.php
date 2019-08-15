<?php
namespace Infomodus\Upsap\Model\Config;

class DestinationType extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'Auto', 'value' => 0],
            ['label' => 'By Company Field', 'value' => 3],
            ['label' => 'Residential', 'value' => 1],
            ['label' => 'Commercial', 'value' => 2],

        ];
        return $c;
    }
}