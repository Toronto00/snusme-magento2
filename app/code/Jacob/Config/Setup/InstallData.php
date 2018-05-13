<?php
namespace Jacob\Config\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Customer\Setup\CustomerSetupFactory;

class InstallData implements InstallDataInterface
{
    protected $config;
    protected $eavSetupFactory;
    protected $eavConfig;
    protected $attributeSetFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        ConfigInterface $config
    ) {
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->config               = $config;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eav = $this->eavSetupFactory
            ->create(
                ['setup' => $setup]
            );

        $attributes = [
            'sub_heading' => [
                'label'         => 'Sub heading',
                'type'          => 'varchar',
                'input'         => 'text'
                'filterable'    => 0,
                'visible_on_front' => false,
            ],
            'strength' => [
                'label'         => 'Strength',
                'type'          => 'int',
                'input'         => 'select',
                'filterable'    => 2,
                'visible_on_front' => true,
                'option'        => [
                    'values' => [
                        'Mild',
                        'Normal',
                        'Strong',
                        'Extra strong',
                        'Super strong',
                        'Nicotine free'
                    ]
                ]
            ],
            'brand' => [
                'label'         => 'Brand',
                'type'          => 'int',
                'input'         => 'select',
                'filterable'    => 2,
                'visible_on_front' => true,
                'option'        => [
                    'values' => [
                        'Göteborgs Rapé',
                        'General',
                        'Catch',
                        'Ettan',
                        'Grov',
                        'Onico',
                        'Kronan',
                        'Tre Ankare',
                        'XRANGE',
                        'Kaliber',
                        'Röda Lacket',
                        'G3',
                        'G4',
                        'Granit',
                        'Knox',
                        'Offroad',
                        'Phantom',
                        'Skruf',
                        'Thunder'
                    ]
                ]
            ],
            'type' => [
                'label'         => 'Type',
                'type'          => 'int',
                'input'         => 'select',
                'filterable'    => 2,
                'visible_on_front' => true,
                'option'        => [
                    'values' => [
                        'Original Portion',
                        'White Portion',
                        'Loose'
                    ]
                ]
            ],
            'format' => [
                'label'         => 'Format',
                'type'          => 'int',
                'input'         => 'select',
                'filterable'    => 2,
                'visible_on_front' => true,
                'option'        => [
                    'values' => [
                        'Super Slim',
                        'Slim',
                        'Large',
                        'Mini'
                    ]
                ]
            ],
            'taste' => [
                'label'         => 'Taste',
                'type'          => 'int',
                'input'         => 'select',
                'filterable'    => 2,
                'visible_on_front' => true,
                'option'        => [
                    'values' => [
                        'Mint',
                        'Citrus',
                        'Fruits',
                        'Licorice'
                    ]
                ]
            ],
            'table_of_content' => [
                'label'         => 'Table of Content',
                'type'          => 'text',
                'input'         => 'textarea',
                'filterable'    => 0,
                'visible_on_front' => true,
            ],
        ];

        foreach ($attributes as $code => $data) {
            $eav->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $code,
                [
                    'group'                     => 'Content',
                    'type'                      => $data['type'],
                    'label'                     => $data['label'],
                    'input'                     => $data['input'],
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'searchable'                => true,
                    'is_filterable'             => $data['filterable'],
                    'is_filterable_in_search'   => $data['filterable'] > 0 ? 1 : 0,
                    'comparable'                => true,
                    'unique'                    => false,
                    'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable,grouped',
                    'visible_on_front'          => $data['visible_on_front'],
                    'used_in_product_listing'   => false,
                    'option'                    => isset($data['option']) ? $data['option'] : false,
                ]
            );
        }
    }
}
