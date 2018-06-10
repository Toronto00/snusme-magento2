<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin;

use Magento\Store\Model\ScopeInterface;

class LayoutProcessor
{
    /**
     * @var array
     */
    protected $orderFixes = [];

    /**
     * @var \Amasty\Checkout\Helper\Onepage
     */
    private $onepageHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Checkout\Model\ModuleEnable
     */
    private $moduleEnable;

    function __construct(
        \Amasty\Checkout\Helper\Onepage\Proxy $onepageHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Checkout\Model\ModuleEnable $moduleEnable
    ) {
        $this->onepageHelper = $onepageHelper;
        $this->scopeConfig = $scopeConfig;
        $this->moduleEnable = $moduleEnable;
    }

    /**
     * @param $field
     * @param $order
     */
    public function setOrder($field, $order)
    {
        $this->orderFixes[$field] = $order;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @return mixed
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if ($this->scopeConfig->isSetFlag('amasty_checkout/general/enabled', ScopeInterface::SCOPE_STORE)) {
            $layoutRoot = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children'];

            foreach ($this->orderFixes as $code => $order) {
                $layoutRoot[$code]['sortOrder'] = $order;
            }

            foreach ($result['components']['checkout']['children']['steps']['children']['billing-step']['children']
                     ['payment']['children']['payments-list']['children'] as &$paymentMethod) {
                $paymentMethod['template'] = 'Amasty_Checkout/billing-address';
            }

            $renderPaymentMethods = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']
                                    ['payment']['children']['renders']['children'];

            if (isset($renderPaymentMethods['braintree'])) {
                $renderPaymentMethods['braintree']['component'] = 'Amasty_Checkout/js/view/checkout/payment/braintree';
            }

            if (!$this->moduleEnable->isRewardsPointEnable()) {
                unset($result['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']['afterMethods']['children']['rewards']);
            } else {
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']['rewards'] += $this->onepageHelper->getRewardsData();
            }
        }

        return $result;
    }
}
