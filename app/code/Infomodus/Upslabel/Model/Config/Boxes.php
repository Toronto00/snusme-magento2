<?php
namespace Infomodus\Upslabel\Model\Config;

use Infomodus\Upslabel\Helper\Config;
use Magento\Framework\Data\OptionSourceInterface;

class Boxes extends Config implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $storeId = null;
        $c = [['label' => __('--PLEASE SELECT--'), 'value' => '']];
        for ($i = 1; $i <= 15; $i++) {
            if ($this->getStoreConfig('upslabel/dimansion_' . $i . '/enable', $storeId) == 1) {
                $c[] = ['label' => $this->getStoreConfig('upslabel/dimansion_' . $i . '/dimansionname', $storeId),
                    'value' => $this->getStoreConfig('upslabel/dimansion_' . $i . '/outer_width', $storeId) . 'x'
                    .$this->getStoreConfig('upslabel/dimansion_' . $i . '/outer_height', $storeId) . 'x'
                    .$this->getStoreConfig('upslabel/dimansion_' . $i . '/outer_length', $storeId)
                ];
            }
        }

        return $c;
    }
}