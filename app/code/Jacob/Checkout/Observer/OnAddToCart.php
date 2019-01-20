<?php
/**
 */
namespace Jacob\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class OnAddToCart implements ObserverInterface
{
    /**
     *
     */
    protected $_messageManager  = null;

    /**
     *
     */
    protected $_productRepo     = null;

    /**
     *
     */
    protected $_checkoutSession = null;

    /**
     *
     */
    protected $_helper          = null;

    /**
     * Constuct with dependencies
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Jacob\Checkout\Helper\Data $helper
    )
    {
        $this->_messageManager  = $messageManager;
        $this->_helper          = $helper;
        $this->_productRepo     = $productRepository;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Execute observer events
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$observer->getRequest()->getPost()) {
            return $this;
        }

        $options    = $observer->getRequest()->getPost('options');
        $qty        = $observer->getRequest()->getPost('qty') ?? 1;
        $productId  = $observer->getRequest()->getPost('product');
        $product    = $this->_productRepo->getById($productId);

        /**
         * An array with a single item of the desired product
         * in case we have a situation where we want to add multiple
         * products
         */
        $request = [
            [
                'product'   => $product,
                'qty'       => $qty,
                'isRoll'    => $this->_isRoll($options, $product)
            ]
        ];

        if (!$this->_helper->canAddWeightToCart($request)) {
            $this->_checkoutSession->setDisplayOverlay(true);
            $observer->getRequest()->setParam('product', false);
        }

        return $this;
    }

    protected function _isRoll($options, $product)
    {
        foreach ($product->getOptions() as $productOption) {
            if (!$this->_helper->isSizeOptionName($productOption->getTitle())) {
                continue;
            }

            $optionKey  = $productOption->getId();

            if (!isset($options[$optionKey])) {
                continue;
            }

            $optionId       = $options[$optionKey];
            $optionValue    = $productOption->getValueById($optionId);

            if (!$this->_helper->isRollOption($optionValue->getDefaultTitle())) {
                continue;
            }

            return true;
        }

        return false;
    }
}