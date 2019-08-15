<?php

namespace Infomodus\Caship\Model\Config;
class ShippingCompany extends \Infomodus\Caship\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($isCustom = true)
    {
        $isAnyCarrier = false;
        if (
            $this->isModuleOutputEnabled("Infomodus_Upslabel")
            || $this->isModuleOutputEnabled("Infomodus_Dhllabel")
            || $this->isModuleOutputEnabled("Infomodus_Fedexlabel")
        ) {
            $isAnyCarrier = true;
        }

        $arr = [];
        if ($isCustom === true) {
            $arr[] = ['value' => 'custom', 'label' => 'Custom'];
        }

        if ($isAnyCarrier == false || $this->isModuleOutputEnabled("Infomodus_Upslabel")) {
            $arr[] = ['value' => 'ups', 'label' => 'UPS'];
        }

        if ($isAnyCarrier == false || $this->isModuleOutputEnabled("Infomodus_Dhllabel")) {
            $arr[] = ['value' => 'dhl', 'label' => 'DHL'];
        }

        if ($isAnyCarrier == false || $this->isModuleOutputEnabled("Infomodus_Fedexlabel")) {
            $arr[] = ['value' => 'fedex', 'label' => 'FedEx'];
        }

        return $arr;
    }
}