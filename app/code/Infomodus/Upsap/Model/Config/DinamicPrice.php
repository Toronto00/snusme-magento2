<?php
namespace Infomodus\Upsap\Model\Config;

class DinamicPrice extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array(
            array('label' => 'Static', 'value' => 0),
            array('label' => 'UPS', 'value' => 1),
        );
    }
}