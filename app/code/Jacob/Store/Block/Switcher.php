<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store and language switcher block
 */
namespace Jacob\Store\Block;

use Magento\Directory\Helper\Data;
use Magento\Store\Model\Group;

/**
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Store\Block\Switcher
{
    /**
     * Available stores and languages
     *
     * @var array
     */
    protected $_matrix = [];

    protected $_labelMap = [
        'en' => 'English',
        'de' => 'Swiss'
    ];

    /**
     * Constructs
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $data);
    }

    public function getStoreMatrix()
    {
        if (count($this->_matrix) > 0) {
            return $this->_matrix;
        }

        $currencies = [];
        $languages = [];
        $websites = $this->_storeManager->getWebsites();
        $currentStore = $this->_storeManager->getStore();
        $currentWebsite = $this->_storeManager->getWebsite();

        list($currentCurrency, $currentLanguage) = explode('_', $currentStore->getCode());
        $activeWebsites = [
            'usd',
            'chf'
        ];

        // Websites are separated by currency
        foreach ($websites as $website) {
            if (!in_array($website->getCode(), $activeWebsites)) {
                continue;
            }

            $websiteStores = $website->getStores();
            $targetStore = null;

            foreach ($websiteStores as $websiteStore) {
                if ($websiteStore->getCode() == "{$website->getCode()}_{$currentLanguage}") {
                    $targetStore = $websiteStore;
                }
            }

            $currencies[$website->getCode()] = [
                'currency'  => $website->getCode(),
                'lang'      => $currentLanguage,
                'code'      => "{$website->getCode()}_{$currentLanguage}",
                'store'     => $targetStore
            ];
        }

        // Stores are separated by locale
        foreach ($currentWebsite->getStores() as $store) {
            list($storeCurrency, $storeLanguage) = explode('_', $store->getCode());

            $languages[$store->getCode()] = [
                'currency'  => $currentCurrency,
                'lang'      => $storeLanguage,
                'label'     => $this->_labelMap[$storeLanguage],
                'code'      => $store->getCode(),
                'store'     => $store,
                'flag'      => $this->getViewFileUrl("images/{$storeLanguage}.png")
            ];
        }

        $this->_matrix = [
            'currencies'    => $currencies,
            'languages'     => $languages,
            'current'       => [
                'currency' => $currentCurrency,
                'language' => $this->_labelMap[$currentLanguage],
                'flag'     => $this->getViewFileUrl("images/{$currentLanguage}.png")
            ]
        ];

        return $this->_matrix;
    }
}