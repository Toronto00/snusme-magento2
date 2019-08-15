<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Upsap\Model\Config;
class Upsmethod implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'UPS Next Day Air', 'value' => '01'],
            ['label' => 'UPS Second Day Air', 'value' => '02'],
            ['label' => 'UPS Ground', 'value' => '03'],
            ['label' => 'UPS Three-Day Select', 'value' => '12'],
            ['label' => 'UPS Next Day Air Saver', 'value' => '13'],
            ['label' => 'UPS Next Day Air Early A.M. SM', 'value' => '14'],
            ['label' => 'UPS Second Day Air A.M.', 'value' => '59'],
            ['label' => 'UPS Saver', 'value' => '65'],
            ['label' => 'UPS Worldwide ExpressSM', 'value' => '07'],
            ['label' => 'UPS Worldwide ExpeditedSM', 'value' => '08'],
            ['label' => 'UPS Standard', 'value' => '11'],
            ['label' => 'UPS Worldwide Express PlusSM', 'value' => '54'],
            ['label' => 'UPS Today StandardSM', 'value' => '82'],
            ['label' => 'UPS Today Dedicated CourrierSM', 'value' => '83'],
            ['label' => 'UPS Today Express', 'value' => '85'],
            ['label' => 'UPS Today Express Saver', 'value' => '86'],
            ['label' => 'UPS Access Point™ Economy', 'value' => '70'],
        ];
    }

    public function getUpsMethods()
    {
        return [
            '01' => 'UPS Next Day Air',
            '02' => 'UPS Second Day Air',
            '03' => 'UPS Ground',
            '07' => 'UPS Worldwide ExpressSM',
            '08' => 'UPS Worldwide ExpeditedSM',
            '11' => 'UPS Standard',
            '12' => 'UPS Three-Day Select',
            '13' => 'UPS Next Day Air Saver',
            '14' => 'UPS Next Day Air Early A.M. SM',
            '54' => 'UPS Worldwide Express PlusSM',
            '59' => 'UPS Second Day Air A.M.',
            '65' => 'UPS Saver',
            '82' => 'UPS Today StandardSM',
            '83' => 'UPS Today Dedicated CourrierSM',
            '85' => 'UPS Today Express',
            '86' => 'UPS Today Express Saver',
            '70' => 'UPS Access Point™ Economy',
        ];
    }

    public function getUpsMethodName($code = '')
    {
        $c = [
            '01' => 'UPS Next Day Air',
            '02' => 'UPS Second Day Air',
            '03' => 'UPS Ground',
            '07' => 'UPS Worldwide ExpressSM',
            '08' => 'UPS Worldwide ExpeditedSM',
            '11' => 'UPS Standard',
            '12' => 'UPS Three-Day Select',
            '13' => 'UPS Next Day Air Saver',
            '14' => 'UPS Next Day Air Early A.M. SM',
            '54' => 'UPS Worldwide Express PlusSM',
            '59' => 'UPS Second Day Air A.M.',
            '65' => 'UPS Saver',
            '82' => 'UPS Today StandardSM',
            '83' => 'UPS Today Dedicated CourrierSM',
            '85' => 'UPS Today Express',
            '86' => 'UPS Today Express Saver',
            '70' => 'UPS Access Point™ Economy',
        ];
        if (array_key_exists($code, $c)) {
            return $c[$code];
        } else {
            return false;
        }
    }
}