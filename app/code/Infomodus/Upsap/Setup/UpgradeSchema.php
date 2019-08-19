<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Upsap\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->addColumnTimeInTransit($setup);
            $this->addColumnAddDay($setup);
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->addAdded($setup);
        }

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->addFreeShipping($setup);
        }

        if (version_compare($context->getVersion(), '2.3.1', '<')) {
            $this->addUserGroups($setup);
        }

        if (version_compare($context->getVersion(), '2.3.3', '<')) {
            $this->addAppuId($setup);
        }

        if (version_compare($context->getVersion(), '2.3.4', '<')) {
            $this->addOrderIncrementId($setup);
        }

        if (version_compare($context->getVersion(), '2.3.6', '<')) {
            $this->addIsStoreAll($setup);
        }

        if (version_compare($context->getVersion(), '2.3.7', '<')) {
            $this->addTitShowFormat($setup);
        }

        if (version_compare($context->getVersion(), '2.3.8', '<')) {
            $this->addIsProdAllow($setup);
        }

        if (version_compare($context->getVersion(), '2.3.9', '<')) {
            $this->addTitCloseHour($setup);
        }

        $setup->endSetup();
    }

    protected function addTitCloseHour(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'tit_close_hour',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '20',
                    'default' => '24:00',
                    'comment' => 'tit close hour'
                ]
            );
        }
    }

    protected function addColumnTimeInTransit(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'time_in_transit',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'UPS Time in transit'
                ]
            );
        }
    }

    protected function addColumnAddDay(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'add_day',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '3,1',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Add day'
                ]
            );
        }
    }

    protected function addAdded(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'added_value_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '50',
                    'nullable' => false,
                    'default' => 'static',
                    'comment' => 'Added price type'
                ]
            );
            $setup->getConnection()->addColumn($tableName,
                'added_value',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '5,2',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Added price value'
                ]
            );
        }
    }

    protected function addFreeShipping(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'free_shipping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 2,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Allow Free Shipping'
                ]
            );
        }
    }

    protected function addUserGroups(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'user_group_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'User Groups'
                ]
            );
        }
    }

    protected function addAppuId(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapaccesspoint');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'appu_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '50',
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'AppuId'
                ]
            );
        }
    }

    protected function addOrderIncrementId(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapaccesspoint');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'order_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Order Increment Id'
                ]
            );
        }
    }

    protected function addIsStoreAll(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'is_store_all',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 1,
                    'default' => 1,
                    'nullable' => false,
                    'comment' => 'Fedex is store id'
                ]
            );
            $setup->getConnection()->modifyColumn(
                $tableName,
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Fedex store ids',
                ]
            );
        }
    }

    protected function addTitShowFormat(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'tit_show_format',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => false,
                    'default' => 'days',
                    'comment' => 'tit show format'
                ]
            );
        }
    }

    protected function addIsProdAllow(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upsapshippingmethod');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'is_prod_allow',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'prod attribute code for allow'
                ]
            );
        }
    }
}
