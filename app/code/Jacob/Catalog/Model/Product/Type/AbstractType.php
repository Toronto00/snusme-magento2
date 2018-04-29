<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jacob\Catalog\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * Abstract model for product type implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractType extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Check if product can be potentially buyed from the category page or some other list
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @since 101.1.0
     */
    public function isPossibleBuyFromList($product)
    {
        return true;
    }
}
