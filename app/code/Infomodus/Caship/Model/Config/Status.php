<?php
namespace Infomodus\Caship\Model\Config;
class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => 'Enabled'],
            ['value' => 0, 'label' => 'Disabled']
        ];
    }
}