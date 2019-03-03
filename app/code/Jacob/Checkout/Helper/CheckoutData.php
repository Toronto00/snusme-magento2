<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Jacob\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class CheckoutData extends \Amasty\Checkout\Helper\CheckoutData
{

    public function __construct(
        Context $context,
        \Amasty\Checkout\Model\Gift\Messages $giftMessages,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amasty\Checkout\Model\Subscription $subscription,
        \Amasty\Checkout\Model\FeeRepository $feeRepository,
        \Amasty\Checkout\Model\ResourceModel\Delivery $deliveryResource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Amasty\Checkout\Model\Delivery $delivery,
        \Magento\Framework\Registry $registry,
        \Amasty\Checkout\Model\Account $account
    ) {
        parent::__construct(
            $context,
            $giftMessages,
            $checkoutSession,
            $subscription,
            $feeRepository,
            $deliveryResource,
            $orderFactory,
            $delivery,
            $registry,
            $account
        );
    }

    public function afterPlaceOrder($amcheckout)
    {
        if (!$this->scopeConfig->isSetFlag('amasty_checkout/general/enabled', ScopeInterface::SCOPE_STORE))
            return;

        $quoteId    = $this->checkoutSession->getLastQuoteId();
        $orderId    = $this->checkoutSession->getLastOrderId();
        $order      = $this->orderFactory->create()->load($orderId);
        $shouldSave = false;

        if (isset($amcheckout['subscribe']) && $amcheckout['subscribe']) {
            $email = isset($amcheckout['email']) ? $amcheckout['email'] : null;
            $this->subscription->subscribe($email);
        }

        if (isset($amcheckout['comment']) && $amcheckout['comment'] && !$this->isCommentSet()) {
            $this->addComment($amcheckout['comment'], $order);
            $shouldSave = true;
        }
        if (isset($amcheckout['dob'])) {
            $order->setCustomerDob($amcheckout['dob']);
            $shouldSave = true;
        }

        if ($shouldSave) {
            $order->save();
        }
        if (isset($amcheckout['gift_wrap']) && $amcheckout['gift_wrap']) {
            $fee = $this->feeRepository->getByQuoteId($quoteId);
            if ($fee->getId()) {
                $fee->setOrderId($orderId);
                $this->feeRepository->save($fee);
            }
        }

        if (isset($amcheckout['register']) && $amcheckout['register']) {
            $this->account->create();
        }

        $delivery = $this->delivery->findByQuoteId($quoteId);

        if ($delivery->getId()) {
            $delivery->setData('order_id', $orderId);
            $this->deliveryResource->save($delivery);
        }
    }

    public function addComment($comment, $order = null)
    {
        if (!$order) {
            $lastOrderId = $this->checkoutSession->getLastOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($lastOrderId);
        }

        $history = $order->addStatusHistoryComment($comment);
        $history->setIsVisibleOnFront(true);
        $history->setIsCustomerNotified(true);
        $history->save();
    }
}
