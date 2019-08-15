<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Items controller
 */
abstract class Items extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_conf;
    protected $items;
    protected $session;
    protected $loggerInterface;
    protected $fileFactory;
    protected $methods;
    protected $storeManagerInterface;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Infomodus\Caship\Helper\Config $config
     * @param \Infomodus\Caship\Model\ItemsFactory $items
     * @param \Magento\Backend\Model\Session $session
     * @param \Psr\Log\LoggerInterface $loggerInterface
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Infomodus\Caship\Helper\Config $config,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Infomodus\Caship\Model\ItemsFactory $items,
        \Magento\Backend\Model\Session $session,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Infomodus\Caship\Model\ResourceModel\Items\Collection $methods,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_conf = $config;
        $this->items = $items;
        $this->session = $session;
        $this->loggerInterface = $loggerInterface;
        $this->fileFactory = $fileFactory;
        $this->methods = $methods;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Infomodus_Caship::items')->_addBreadcrumb(__('Items'), __('Items'));
        return $this;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Infomodus_Caship::items');
    }
}
