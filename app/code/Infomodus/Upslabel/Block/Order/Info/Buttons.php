<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block of links in Order view page
 */
namespace Infomodus\Upslabel\Block\Order\Info;

class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/info/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    protected $_conf;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Infomodus\Upslabel\Helper\Config $config,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->_conf = $config;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getRMAUrl($order)
    {
        if ($order->getState() !== \Magento\Sales\Model\Order::STATE_COMPLETE
            || $this->_conf->getStoreConfig('upslabel/return/refundaccess')==0) {
            return null;
        }
        return $this->getUrl('upslabel/rma/edit', ['order_id' => $order->getId()]);
    }
}
