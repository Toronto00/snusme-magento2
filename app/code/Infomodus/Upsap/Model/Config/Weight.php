<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upsap\Model\Config;

class Weight implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('LBS'), 'value' => 'LBS'],
            ['label' => __('KGS'), 'value' => 'KGS'],
        ];
    }
}
