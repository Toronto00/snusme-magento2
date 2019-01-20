<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Jacob\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Cart source
 */
class Weightoverlay extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param ItemPoolInterface $itemPoolInterface
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    private function getSelectedCountry()
    {
        $quote          = $this->_checkoutSession->getQuote();
        $defaultCountry = $this->_scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $sameAsBilling  = $quote->getShippingAddress()->getSameAsBilling();
        $address        = $sameAsBilling === "0" ? $quote->getBillingAddress() : $quote->getShippingAddress();

        return $address->getCountryId() ?? $defaultCountry;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $displayOverlay = $this->_checkoutSession->getDisplayOverlay();

        $this->_checkoutSession->setDisplayOverlay(false);

        return [
            'display_overlay'   => $displayOverlay,
            'selected_country'  => $this->getSelectedCountry()
        ];
    }
}
