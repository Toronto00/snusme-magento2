<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('upsapaccesspoint'))
            ->addColumn(
                'ap_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Name'
            )->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Address'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Update time'
            )->setComment(
                'UPS Access Point'
            );
        $installer->getConnection()->createTable($table);

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('upsapshippingmethod'))
            ->addColumn(
                'upsapshippingmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                ['default' => ''],
                'Title'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                ['default' => ''],
                'Title'
            )->addColumn(
                'upsmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => ''],
                'upsmethod id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1],
                'Name'
            )->addColumn(
                'country_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'country ids'
            )->addColumn(
                'zip_min',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['default' => ''],
                'zip min'
            )->addColumn(
                'zip_max',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['default' => ''],
                'zip max'
            )->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '9,2',
                ['default' => 0],
                'Price'
            )->addColumn(
                'dinamic_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => 0],
                'dinamic price'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => 0],
                'Status'
            )->addColumn(
                'negotiated',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => 0],
                'negotiated'
            )->addColumn(
                'negotiated_amount_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'negotiated amount from'
            )->addColumn(
                'tax',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                2,
                ['default' => 0],
                'tax'
            )->addColumn(
                'showifnot',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['default' => 0],
                'show if not'
            )->addColumn(
                'qty_min',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'qty min'
            )->addColumn(
                'qty_max',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'qty max'
            )->addColumn(
                'amount_min',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '9,2',
                ['default' => 0],
                'amount min'
            )->addColumn(
                'amount_max',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '9,2',
                ['default' => 0],
                'amount max'
            )->addColumn(
                'weight_min',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '5,2',
                ['default' => 0],
                'weight min'
            )->addColumn(
                'weight_max',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '5,2',
                ['default' => 0],
                'weight max'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Update time'
            )->setComment(
                'UPS Access Point methods'
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}