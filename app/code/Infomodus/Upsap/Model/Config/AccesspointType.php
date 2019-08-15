<?php
namespace Infomodus\Upsap\Model\Config;

class AccesspointType extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => __('Hold for Pickup at UPS Access Point'), 'value' => '01'),
            array('label' => __('UPS Access Point Delivery'), 'value' => '02'),
        );
        return $c;
    }
}