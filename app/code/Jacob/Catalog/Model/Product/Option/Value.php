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
        return $this->getPrice(true) + $this->getBaseProductFinalPrice();
    }

    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return float|int
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getBaseProductFinalPrice();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    public function getBaseProductFinalPrice()
    {
        $priceInfo  = $this->getProduct()->getPriceInfo();
        $finalPrice = $priceInfo->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();

        return $finalPrice;
    }

    public function getBaseProductRegularPrice()
    {
        $priceInfo  = $this->getProduct()->getPriceInfo();
        $price      = $priceInfo->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();

        return $price;
    }

    public function hasSpecialPrice()
    {
        $finalPrice     = $this->getBaseProductFinalPrice();
        $regularPrice   = $this->getBaseProductRegularPrice();

        return $finalPrice !== $regularPrice;
    }

    public function getOriginalPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getBaseProductRegularPrice();
            $price = $basePrice + ($basePrice * ($this->_getData(self::KEY_PRICE) / 100));
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }
}
