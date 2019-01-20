<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jacob\Checkout\Controller\Delivery;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Set extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Set delivery country action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if (!$selectedCountry = $this->getRequest()->getPost('selected_country')) {
            return;
        }

        $quote          = $this->_checkoutSession->getQuote();
        $sameAsBilling  = $quote->getShippingAddress()->getSameAsBilling();
        $address        = $sameAsBilling === "0" ? $quote->getBillingAddress() : $quote->getShippingAddress();

        $address->setCountryId($selectedCountry)->save();
    }
}
