<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Upsap\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderCreateBefore implements ObserverInterface
{
    protected $_coreRegistry;
    protected $_conf;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Infomodus\Upsap\Helper\Config $config,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_coreRegistry = $registry;
        $this->_conf = $config;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->_conf->getStoreConfig('carriers/upsap/active', $order->getStoreId()) == 1
            && strpos($order->getShippingMethod(), 'upsap_', 0) !== false
        ) {
            $session = $this->_checkoutSession;
            $street = array();
            if ($session->getUpsapAddLine1()) {
                $shippingAddress = $order->getShippingAddress();

                $street[0] = $session->getUpsapAddLine1();

                if ($session->getUpsapAddLine2()) {
                    $street[1] = $session->getUpsapAddLine2();
                    /*$session->unsUpsapAddLine2();*/

                    if ($session->getUpsapAddLine3()) {
                        $street[1] = $street[1] . ' ' . $session->getUpsapAddLine3();
                        /*$session->unsUpsapAddLine3();*/
                    }
                }

                if ($session->getUpsapCity()) {
                    $shippingAddress->setCity($session->getUpsapCity());
                    /*$session->unsUpsapCity();*/
                }

                if ($session->getUpsapCountry()) {
                    $shippingAddress->setCountry_id($session->getUpsapCountry());
                    /*$session->unsUpsapCountry();*/
                }

                if ($session->getUpsapFax() && trim($session->getUpsapFax()) != 'undefined') {
                    $shippingAddress->setFax($session->getUpsapFax());
                    $shippingAddress->setTelephone($session->getUpsapFax());
                    /*$session->unsUpsapFax();*/
                }

                if ($session->getUpsapPostal()) {
                    $shippingAddress->setPostcode($session->getUpsapPostal());
                    /*$session->unsUpsapPostal();*/

                }

                if ($session->getUpsapName()) {
                    $shippingAddress->setCompany($session->getUpsapName());
                    /*$session->unsUpsapName();*/
                }

                $shippingAddress->setStreet($street);
                $shippingAddress->setId(null);
                $shippingAddress->setCustomerAddressId(null);
                /*$session->unsUpsapAddLine1();*/
                /*if ($session->getUpsapState()) {
                    $session->unsUpsapState();
                }*/
            }
        }
        return $this;
    }
}
