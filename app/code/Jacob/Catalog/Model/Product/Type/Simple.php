<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jacob\Catalog\Model\Product\Type;

/**
 * Simple product type implementation
 */
class Simple extends \Jacob\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
