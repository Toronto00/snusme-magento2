<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Jacob\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Model\AbstractModel;

/**
 * Catalog product option select type model
 *
 * @api
 * @method int getOptionId()
 * @method \Magento\Catalog\Model\Product\Option\Value setOptionId(int $value)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Value extends \Magento\Catalog\Model\Product\Option\Value implements \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface
{
    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return float|int
     */
    public function getFinalPrice()
    {
        $basePrice = $this->getOption()->getProduct()->getFinalPrice();

        return $this->getPrice(true) + $basePrice;
    }
}
