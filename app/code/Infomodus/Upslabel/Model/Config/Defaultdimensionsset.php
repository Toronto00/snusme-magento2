<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upslabel\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Defaultdimensionsset extends \Infomodus\Upslabel\Helper\Config implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $c = [];
        for ($i=1; $i<=15; $i++) {
            if ($this->getStoreConfig('upslabel/dimansion_'.$i.'/enable') == 1) {
                $c[] = ['label' => $this->getStoreConfig('upslabel/dimansion_'.$i.'/dimansionname'), 'value' => $i];
            }
        }
        return $c;
    }

    public function getDimensionSets()
    {
        $c = [];
        for ($i=1; $i<=15; $i++) {
            if ($this->getStoreConfig('upslabel/dimansion_'.$i.'/enable')==1) {
                $c[$i] =  $this->getStoreConfig('upslabel/dimansion_'.$i.'/dimansionname');
            }
        }
        return $c;
    }
}
