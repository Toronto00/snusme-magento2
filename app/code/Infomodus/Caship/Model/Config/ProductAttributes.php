<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Caship\Model\Config;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Option\ArrayInterface;

class ProductAttributes implements ArrayInterface
{
    protected $attributeCollection;

    public function __construct(
        Collection $attributeCollection
    )
    {
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray()
    {
        $coll = $this->attributeCollection->setOrder('main_table.frontend_label', 'ASC');
        $attributes = $coll->load()->getItems();
        $attributeArray = [[
            'label' => __('--NOT SELECTED--'),
            'value' => ''
        ]];

        foreach ($attributes as $attribute) {
            $attributeArray[] = [
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code')
            ];
        }

        return $attributeArray;
    }
}
