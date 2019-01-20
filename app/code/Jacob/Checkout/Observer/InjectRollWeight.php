<?php
/**
 */
namespace Jacob\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class InjectRollWeight implements ObserverInterface
{
    /**
     *
     */
    protected $_helper = null;

    /**
     * Constuct with dependencies
     */
    public function __construct(
        \Jacob\Checkout\Helper\Data $helper
    )
    {
        $this->_helper = $helper;
    }

    /**
     * Execute observer events
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product    = $observer->getProduct();
        $quoteItem  = $observer->getQuoteItem();
        $weight     = $product->getWeight();
        $buyRequest = $quoteItem->getBuyRequest();


        if ($buyRequest->getOptions()) {
            foreach ($product->getOptions() as $productOption) {
                if (!$this->_helper->isSizeOptionName($productOption->getTitle())) {
                    continue;
                }

                $optionKey  = $productOption->getId();
                $options    = $buyRequest->getOptions();

                if (!isset($options[$optionKey])) {
                    continue;
                }

                $optionId       = $options[$optionKey];
                $optionValue    = $productOption->getValueById($optionId);

                if (!$this->_helper->isRollOption($optionValue->getDefaultTitle())) {
                    continue;
                }

                $weight = (float) $weight * 10;
                $weight = (string) $weight;
            }
        }

        $quoteItem->setWeight($weight);

        return $this;
    }
}