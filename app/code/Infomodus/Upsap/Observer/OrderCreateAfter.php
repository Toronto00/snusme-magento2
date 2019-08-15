<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Upsap\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderCreateAfter implements ObserverInterface
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
            if ($session->getUpsapAddLine1()) {
                $accesspoint = $this->_conf->_objectManager->create('Infomodus\Upsap\Model\Points')->getCollection()->addFieldToFilter('order_id', $order->getId());
                if ($accesspoint->count() == 0) {
                    try {
                        $accesspoint = $this->_conf->_objectManager->create('Infomodus\Upsap\Model\Points');
                        $address = array();

                        $address['addLine1'] = $session->getUpsapAddLine1();
                        $session->unsUpsapAddLine1();

                        if ($session->getUpsapAddLine2()) {
                            $address['addLine2'] = $session->getUpsapAddLine2();
                            $session->unsUpsapAddLine2();
                        }

                        if ($session->getUpsapAddLine3()) {
                            $address['addLine3'] = $session->getUpsapAddLine3();
                            $session->unsUpsapAddLine3();
                        }

                        if ($session->getUpsapCity()) {
                            $address['city'] = $session->getUpsapCity();
                            $session->unsUpsapCity();
                        }

                        if ($session->getUpsapCountry()) {
                            $address['country'] = $session->getUpsapCountry();
                            $session->unsUpsapCountry();
                        }

                        if ($session->getUpsapFax()) {
                            $address['fax'] = $session->getUpsapFax();
                            $session->unsUpsapFax();
                        }

                        if ($session->getUpsapState()) {
                            $address['state'] = $session->getUpsapState();
                            $session->unsUpsapState();
                        }

                        if ($session->getUpsapPostal()) {
                            $address['postal'] = $session->getUpsapPostal();
                            $session->unsUpsapPostal();
                        }

                        if ($session->getUpsapAppuId()) {
                            $address['appuId'] = $session->getUpsapAppuId();
                            $session->unsUpsapAppuId();
                        }

                        if ($session->getUpsapName()) {
                            $address['name'] = $session->getUpsapName();
                            $session->unsUpsapName();
                        }

                        $accesspoint->setOrderId($order->getId());
                        $accesspoint->setOrderIncrementId($order->getIncrementId());
                        if (isset($address['appuId'])) {
                            $accesspoint->setAppuId($address['appuId']);
                        }

                        $accesspoint->setAddress(json_encode($address));
                        $accesspoint->save();
                    } catch (\Magento\Framework\DB\Adapter\DuplicateException $e) {
                        $this->_conf->log("Infomodus Upsap Duplicate " . $e->getCode() . ": " . $e->getMessage());
                    } catch (\Exception $e) {
                        $this->_conf->log("Infomodus Upsap Error " . $e->getCode() . ": " . $e->getMessage());
                    }
                } else {
                    $this->_conf->log("Infomodus Upsap : This access point is already exist");
                    /*$this->_conf->log(json_encode($accesspoint->getData()));*/
                }
            }
        }

        return $this;
    }
}
