<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Rudjuk Vitalij
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upslabel\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Defaultaddress extends \Infomodus\Upslabel\Helper\Config implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $c = [];
        for ($i = 1; $i <= 10; $i++) {
            if ($this->getStoreConfig('upslabel/address_' . $i . '/enable') == 1) {
                $c[] = ['label' => __($this->getStoreConfig('upslabel/address_' . $i . '/addressname')), 'value' => $i];
            }
        }
        return $c;
    }

    public function getAddresses()
    {
        $c = [];
        for ($i = 1; $i <= 10; $i++) {
            if ($this->getStoreConfig('upslabel/address_' . $i . '/enable') == 1) {
                $c[$i] = __($this->getStoreConfig('upslabel/address_' . $i . '/addressname'));
            }
        }
        return $c;
    }
}
