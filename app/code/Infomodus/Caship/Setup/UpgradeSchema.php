<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Caship\Setup;

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

        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $this->addColumnIsCountryAll($setup);
        }

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->addColumnTimeInTransit($setup);
            $this->addColumnAddDay($setup);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->addAdded($setup);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->addFreeShipping($setup);
        }

        if (version_compare($context->getVersion(), '1.4.1', '<')) {
            $this->addUserGroups($setup);
        }

        if (version_compare($context->getVersion(), '1.4.3', '<')) {
            $this->addFedexMethodId($setup);
        }

        if (version_compare($context->getVersion(), '1.4.4', '<')) {
            $this->addIsStoreAll($setup);
        }

        if (version_compare($context->getVersion(), '1.4.5', '<')) {
            $this->addTitShowFormat($setup);
        }

        if (version_compare($context->getVersion(), '1.4.6', '<')) {
            $this->addIsProdAllow($setup);
        }

        if (version_compare($context->getVersion(), '1.4.7', '<')) {
            $this->addAppliedExtraPriceFor($setup);
        }

        if (version_compare($context->getVersion(), '1.4.8', '<')) {
            $this->addTitCloseHour($setup);
        }

        if (version_compare($context->getVersion(), '1.4.9', '<')) {
            $this->addRural($setup);
        }

        $setup->endSetup();
    }

    protected function addRural(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'rural',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'length' => '2',
                    'default' => '1',
                    'comment' => 'rural extended'
                ]
            );
        }
    }

    protected function addTitCloseHour(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
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

    protected function addAppliedExtraPriceFor(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'added_value_applied_for',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '20',
                    'default' => 'shipment',
                    'comment' => 'Applied Extra Price for'
                ]
            );
        }
    }

    protected function addColumnTimeInTransit(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
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
        $tableName = $setup->getTable('infomodus_caship');
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

    protected function addColumnIsCountryAll(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'is_country_all',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'is country all'
                ]
            );
        }
    }

    protected function addAdded(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
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
        $tableName = $setup->getTable('infomodus_caship');
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
        $tableName = $setup->getTable('infomodus_caship');
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

    protected function addFedexMethodId(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn($tableName,
                'fedexmethod_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '50',
                    'default' => '',
                    'comment' => 'Fedex method id'
                ]
            );
        }
    }

    protected function addIsStoreAll(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('infomodus_caship');
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
        $tableName = $setup->getTable('infomodus_caship');
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
        $tableName = $setup->getTable('infomodus_caship');
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
