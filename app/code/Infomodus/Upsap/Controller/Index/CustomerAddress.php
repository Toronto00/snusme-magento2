<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Upsap\Controller\Index;

class CustomerAddress extends \Infomodus\Upsap\Controller\Index
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $address = $this->_checkoutSession->getQuote()->getShippingAddress();
        /*$addressId = Mage::app()->getRequest()->getParam('id');
        $address = Mage::getModel('customer/address')->load((int)$addressId);*/
        if(!$address){
            $address = $this->_checkoutSession->getQuote()->getBillingAddress();
        }
        /*print_r($address->getData());*/
        if($address->getAddressId()){
            $address = $this->_customerSession->getCustomer()->getAddressById($address->getAddressId());
        }
        /*print_r($address->getData());*/
        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $region = $this->_conf->objectManager->get('Magento\Directory\Model\Region')->load($address->getRegionId());
            $state_code = $region->getCode();
            $address->setRegion($state_code);
        }
        $result = $this->_resultJsonFactory->create();
        return $result->setData($address->getData());
    }
}
