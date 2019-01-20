<?php
/**
 */
namespace Jacob\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Api\Filter as Filter;

class OnUpdateCart implements ObserverInterface
{
    /**
     *
     */
    protected $_messageManager      = null;

    /**
     *
     */
    protected $_productRepo         = null;

    /**
     *
     */
    protected $_searchCriteria      = null;

    /**
     *
     */
    protected $_checkoutSession     = null;

    /**
     *
     */
    protected $_helper              = null;

    /**
     *
     */
    protected $_productOptionsRepo  = null;

    /**
     * Constuct with dependencies
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Jacob\Checkout\Helper\Data $helper,
        \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository
    )
    {
        $this->_messageManager      = $messageManager;
        $this->_productRepo         = $productRepo;
        $this->_searchCriteria      = $searchCriteriaBuilder;
        $this->_checkoutSession     = $checkoutSession;
        $this->_helper              = $helper;
        $this->_productOptionsRepo  = $productOptionRepository;
    }

    /**
     * Execute observer events
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$observer->getInfo()) {
            return $this;
        }

        $itemIds    = array_keys($observer->getInfo()->getData());
        $items      = $observer->getCart()->getQuote()->getItems();
        $productIds = [];
        $requests   = [];

        foreach ($items as $item) {
            if (in_array($item->getId(), $itemIds)) {
                $productIds[$item->getId()] = $item->getProductId();
                $productQty[$item->getId()] = $item->getQty();
                $requests[$item->getId()]   = $item->getBuyRequest();
            }
        }

        if (!$productIds) {
            return $this;
        }

        $request            = [];
        $searchCriteria     = $this->_searchCriteria->addFilter('entity_id', $productIds, 'in')->create();
        $productCollection  = $this->_productRepo
            ->getList($searchCriteria)
            ->getItems();

        foreach ($observer->getInfo()->getData() as $quoteItemId => $updateRequest) {
            if (!isset($productIds[$quoteItemId])) {
                continue;
            }

            $productId      = $productIds[$quoteItemId];
            $product        = $productCollection[$productId];
            $qty            = $updateRequest['qty'] - $productQty[$quoteItemId];
            $buyRequest     = $requests[$quoteItemId];
            $productOptions = $this->_productOptionsRepo->getProductOptions($product);
            $request[]      = [
                'product'   => $product,
                'qty'       => $qty,
                'isRoll'    => $this->_isRoll($buyRequest, $productOptions)
            ];
        }

        if (!$this->_helper->canAddWeightToCart($request)) {
            $this->_checkoutSession->setDisplayOverlay(true);
            throw new \Magento\Framework\Exception\LocalizedException(__('Weight exceed shipping maximum to your country.'));
        }

        return $this;
    }

    protected function _isRoll($buyRequest, $options)
    {
        if ($buyRequest->getOptions()) {
            foreach ($options as $productOption) {
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

                return true;
            }
        }

        return false;
    }
}