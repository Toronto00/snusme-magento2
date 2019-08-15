<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upsap\Model\Config;
class Stores extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    private $stores = null;
    public function toOptionArray()
    {
        $c = [];
        $manager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        foreach ($manager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $c[] = ['label' => $store->getName()." (".$website->getName()." \\ ".$group->getName().")", 'value' => $store->getId()];
                }
            }
        }

        return $c;
    }

    public function getStores()
    {
        $manager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        foreach ($manager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $c[$store->getId()] = $store->getName()." (".$website->getName()." \\ ".$group->getName().")";
                }
            }
        }
        return $c;
    }

    public function getStoreNameById($storeId)
    {
        if($this->stores === null) {
            $this->stores = [];
            $manager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
            foreach ($manager->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $this->stores[$store->getId()] = $store->getName() . " (" . $website->getName() . " \\ " . $group->getName() . ")";
                    }
                }
            }
        }

        if(isset($this->stores[$storeId])) {
            return $this->stores[$storeId];
        }

        return "";
    }
}