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
 * @package   mirasvit/module-search
 * @version   1.0.76
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\Select\QueryModifierFactory;

class Recurring implements InstallSchemaInterface
{
    private $fieldDataConverterFactory;

    private $queryModifierFactory;

    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->upgradeSerializeToJson($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function upgradeSerializeToJson(SchemaSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'searchautocomplete/general/index',
                        'search/advanced/wildcard_exceptions',
                        'search/advanced/replace_words',
                        'search/advanced/not_words',
                        'search/advanced/long_tail_expressions',
                    ],
                ],
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }
}
