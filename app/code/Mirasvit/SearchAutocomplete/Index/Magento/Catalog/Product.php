<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Index\Magento\Catalog;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\ReviewFactory;
use Mirasvit\SearchAutocomplete\Model\Config;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends AbstractIndex
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ReviewRenderer
     */
    private $reviewRenderer;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Config $config,
        ReviewFactory $reviewFactory,
        ReviewRenderer $reviewRenderer,
        ImageHelper $imageHelper,
        CatalogHelper $catalogHelper,
        PricingHelper $pricingHelper,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->reviewFactory = $reviewFactory;
        $this->reviewRenderer = $reviewRenderer;
        $this->imageHelper = $imageHelper;
        $this->catalogHelper = $catalogHelper;
        $this->pricingHelper = $pricingHelper;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];
        $categoryId = intval($this->request->getParam('cat'));

        $collection = $this->getCollection();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('description');

        if ($categoryId) {
            $om = ObjectManager::getInstance();
            $category = $om->create('Magento\Catalog\Model\Category')->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        if ($this->config->isShowRating()) {
            $this->reviewFactory->create()->appendSummary($collection);
        }
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $items[] = $this->mapProduct($product);
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    private function mapProduct($product)
    {

        $item = [
            'name'        => $product->getName(),
            'url'         => $product->getProductUrl(),
            'sku'         => null,
            'description' => null,
            'image'       => null,
            'price'       => null,
            'rating'      => null,
            'optimize'    => false,
        ];

        if ($this->config->isShowShortDescription()) {
            $item['description'] = html_entity_decode(
                strip_tags($product->getDataUsingMethod('description'))
            );
        }

        if ($this->config->isShowSku()) {
            $item['sku'] = html_entity_decode(
                strip_tags($product->getDataUsingMethod('sku'))
            );
        }

        $image = false;
        if ($product->getImage() && $product->getImage() != 'no_selection') {
            $image = $product->getImage();
        } elseif ($product->getSmallImage() && $product->getSmallImage() != 'no_selection') {
            $image = $product->getSmallImage();
        }

        if ($this->config->isShowImage() && $image) {
            $item['image'] = $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($image)
                ->resize(65 * 2, 80 * 2)
                ->getUrl();
        }

        if ($this->config->isShowPrice()) {
            $product->setData('final_price', null); #reset wrong calculated price

            $item['price'] = $this->catalogHelper->getTaxPrice($product, $product->getFinalPrice());

            if ($product->getFinalPrice() > $item['price']) {
                $item['price'] = $product->getFinalPrice();
            }
            if ($product->getMinimalPrice() > $item['price']) {
                $item['price'] = $product->getMinimalPrice();
            }

            $item['price'] = $this->pricingHelper->currency($item['price'], false, false);
        }

        if ($this->config->isShowRating()) {
            $item['rating'] = $this->reviewRenderer
                ->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
        }

        $om = ObjectManager::getInstance();
        /** @var \Magento\Catalog\Block\Product\ListProduct $productBlock */
        $productBlock = $om->create('Magento\Catalog\Block\Product\ListProduct');
        $item['cart'] = [
            'visible' => $this->config->isShowCartButton(),
            'label'   => __('Add to Cart'),
            'params'  => $productBlock->getAddToCartPostParams($product),
        ];

        return $item;
    }
}