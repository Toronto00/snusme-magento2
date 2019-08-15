<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upsap\Model\Config;

class ProductAttributes extends \Infomodus\Upsap\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $coll = $this->getModel()->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class)
            ->setOrder('main_table.frontend_label', 'ASC');
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
