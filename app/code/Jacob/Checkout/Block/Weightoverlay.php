<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jacob\Checkout\Block;

class Weightoverlay extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    private $_countryOptions;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $_countryCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $_serializer;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_scopeConfig = $scopeConfig;
        $this->_countryCollectionFactory = $countryCollection;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_serializer  = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Get country options list.
     *
     * @return array
     */
    private function getCountryOptions()
    {
        if (!isset($this->_countryOptions)) {
            $this->_countryOptions = $this->_countryCollectionFactory->create()->loadByStore(
                $this->_storeManager->getStore()->getId()
            )->toOptionArray();
        }

        return $this->_countryOptions;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'setDeliveryUrl'    => $this->getUrl('smc/delivery/set'),
            'countryOptions'    => $this->getCountryOptions()
        ];
    }

    /**
     * @return string
     */
    public function getSerializedConfig()
    {
        return $this->_serializer->serialize($this->getConfig());
    }
}
