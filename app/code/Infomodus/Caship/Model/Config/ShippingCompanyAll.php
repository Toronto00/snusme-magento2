<?php

namespace Infomodus\Caship\Model\Config;
class ShippingCompanyAll extends \Infomodus\Caship\Helper\Config implements \Magento\Framework\Option\ArrayInterface
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
            $arr[] = ['value' => 'ups', 'label' => $this->getStoreConfig('carriers/ups/title')];
            if ($this->isModuleOutputEnabled("Infomodus_Upslabel")) {
                $arr[] = ['value' => 'upsinfomodus', 'label' => 'UPS Infomodus'];
            }
        }

        if ($isAnyCarrier == false || $this->isModuleOutputEnabled("Infomodus_Dhllabel")) {
            $arr[] = ['value' => 'dhl', 'label' => $this->getStoreConfig('carriers/dhl/title')];
            if ($this->isModuleOutputEnabled("Infomodus_Dhllabel")) {
                $arr[] = ['value' => 'dhlinfomodus', 'label' => 'DHL Infomodus'];
            }
        }

        if ($isAnyCarrier == false || $this->isModuleOutputEnabled("Infomodus_Fedexlabel")) {
            $arr[] = ['value' => 'fedex', 'label' => $this->getStoreConfig('carriers/fedex/title')];
            if ($this->isModuleOutputEnabled("Infomodus_Fedexlabel")) {
                $arr[] = ['value' => 'fedexinfomodus', 'label' => 'FedEx Infomodus'];
            }
        }

        return $arr;
    }
}