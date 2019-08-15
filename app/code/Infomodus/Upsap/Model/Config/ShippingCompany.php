<?php
namespace Infomodus\Upsap\Model\Config;
class ShippingCompany extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $arr = [];
        $arr[] = ['value' => 'ups', 'label' => 'Default Magento UPS module'];
        if($this->isModuleOutputEnabled("Infomodus_Upslabel")) {
            $arr[] = ['value' => 'upsinfomodus', 'label' => 'UPS Shipping Manager Pro'];
        }
        return $arr;
    }
}