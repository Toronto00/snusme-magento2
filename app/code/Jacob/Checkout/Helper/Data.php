<?php

namespace Jacob\Checkout\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Attribute name for calculating weight
     *
     * @var string
     */
    const WEIGHT_ATTRIBUTE      = 'nicotine_weight';

    /**
     *
     */
    protected $_checkoutSession = null;

    /**
     *
     */
    protected $_scopeConfig     = null;

    /**
     *
     */
    protected $_totalWeight     = 0;

    /**
     * Constuct with dependencies
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig     = $scopeConfig;
    }

    /**
     * Checks current cart weight and sees if a given buy request (product + qty)
     * can be added on top of it
     *
     * @param array $buyRequest
     * @return boolean
     */
    public function canAddWeightToCart(array $buyRequest)
    {
        $quote = $this->_checkoutSession->getQuote();
        $maxWeightStructure = $this->_scopeConfig->getValue(
            'snusme/checkout/max_nicotine_weight',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$quote || !$maxWeightStructure) {
            return true;
        }

        $defaultCountry = $this->_scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $sameAsBilling      = $quote->getShippingAddress()->getSameAsBilling();
        $address            = $sameAsBilling ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $country            = $address->getCountryId() ?? $defaultCountry;
        $maxWeightStructure = unserialize($maxWeightStructure);

        if (!array_key_exists($country, $maxWeightStructure)) {
            return true;
        }

        if ($this->getQuoteTotalWeight() + $this->getRequestWeight($buyRequest) > $maxWeightStructure[$country]) {
            return false;
        }

        return true;
    }

    /**
     * Get weight of all products in current quote in session
     *
     * @return int
     */
    public function getQuoteTotalWeight()
    {
        if ($this->_totalWeight) {
            return $this->_totalWeight;
        }

        $quote = $this->_checkoutSession->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if (!$item->getProduct() || !$item->getProduct()->getData(self::WEIGHT_ATTRIBUTE)) {
                continue;
            }

            $modifier = 1;

            if ($item->getBuyRequest() && $options = $item->getBuyRequest()->getOptions()) {
                foreach ($item->getProduct()->getOptions() as $productOption) {
                    if (!$this->isSizeOptionName($productOption->getTitle())) {
                        continue;
                    }

                    $optionKey  = $productOption->getId();

                    if (!isset($options[$optionKey])) {
                        continue;
                    }

                    $optionId       = $options[$optionKey];
                    $optionValue    = $productOption->getValueById($optionId);

                    if (!$this->isRollOption($optionValue->getDefaultTitle())) {
                        continue;
                    }

                    $modifier = 10;
                }
            }

            $this->_totalWeight += ($item->getProduct()->getData(self::WEIGHT_ATTRIBUTE) * $modifier) * $item->getQty();
        }

        return $this->_totalWeight;
    }

    /**
     * Get weight of a "current request" of a product and it's qty
     *
     * @param array $request
     * @return int
     */
    public function getRequestWeight(array $request)
    {
        if (!$request) {
            return 0;
        }

        $weight = 0;

        foreach ($request as $requestItem) {
            if (!isset($requestItem['qty']) || !isset($requestItem['product'])) {
                continue;
            }

            $product        = $requestItem['product'];
            $qty            = $requestItem['qty'];
            $isRoll         = $requestItem['isRoll'] ?? false;
            $weightModifier = $isRoll ? 10 : 1;

            if (!$product->getData(self::WEIGHT_ATTRIBUTE)) {
                continue;
            }

            $weight += ($product->getData(self::WEIGHT_ATTRIBUTE) * $weightModifier) * $qty;
        }

        return $weight;
    }

    /**
     * Is given name a variation of product size (single or roll)
     *
     * @param string $name
     * @return boolean
     */
    public function isSizeOptionName($name)
    {
        if (!$name) {
            return false;
        }

        $lowerName = mb_strtolower($name);
        return in_array($lowerName, ['storlek', 'size', 'package', 'pack', 'roll']);
    }

    /**
     * Is the name of the given option stock or roll
     *
     * @param string $name
     * @return boolean
     */
    public function isRollOption($name)
    {
        if (!$name) {
            return false;
        }

        $lowerName = mb_strtolower($name);
        return in_array($lowerName, ['stock', 'roll', '1 stock', '1 roll']);
    }
}