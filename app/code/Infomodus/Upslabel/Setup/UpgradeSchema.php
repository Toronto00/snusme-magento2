<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Upslabel\Setup;

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
            $this->createAccountTable($setup);
        }
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->addColumnType2($setup);
            $this->addColumnXmllog($setup);
        }
        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->addColumnPrice($setup);
        }
        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->addColumnOrderAndSCIncrement($setup);
        }
        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addColumnShipmentidentificationnumber2($setup);
        }
        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->createPickupTable($setup);
        }
        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->createConformityTable($setup);
        }
        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            $this->addColumnLabelStoreId($setup);
        }
        if (version_compare($context->getVersion(), '8.5.5', '<')) {
            $this->changeLabelName($setup);
        }


        $setup->endSetup();
    }

    public function changeLabelName(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->changeColumn(
                $tableName,
                'labelname',
                'labelname',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                ]
            );
        }
    }

    public function addColumnLabelStoreId(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Store Id'
                ]
            );
        }
    }
    public function addColumnShipmentidentificationnumber2(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'shipmentidentificationnumber_2',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Shipment Identification number origin'
                ]
            );
        }
    }
    public function addColumnOrderAndSCIncrement(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'order_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Order Increment Id'
                ]
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'shipment_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Shipment or Creditmemo Increment Id'
                ]
            );
        }
    }
    public function addColumnPrice(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '7,2',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Price'
                ]
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'currency',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 3,
                    'nullable' => false,
                    'default' => 'USD',
                    'comment' => 'Currency'
                ]
            );
        }
    }
    public function addColumnType2(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'type_2',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => false,
                    'default' => 'shipment',
                    'comment' => 'Type 2'
                ]
            );
        }
    }
    public function addColumnXmllog(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabel');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'xmllog',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Xml log'
                ]
            );
        }
    }

    public function createConformityTable(SchemaSetupInterface $setup)
    {
        /*
         * */

        $tableName = $setup->getTable('upslabelconformity');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'upslabelaccount'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'conformity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Conformity ID'
            )->addColumn(
                'method_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'method id'
            )->addColumn(
                'upsmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'upsmethod id'
            )->addColumn(
                'country_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'country ids'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => 1],
                'store id'
            )->setComment(
                'UPS Conformity'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function createAccountTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabelaccount');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'upslabelaccount'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Account ID'
            )->addColumn(
                'companyname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Company name'
            )->addColumn(
                'attentionname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Attention name'
            )->addColumn(
                'address1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 1'
            )->addColumn(
                'address2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 2'
            )->addColumn(
                'address3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 3'
            )->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Country'
            )->addColumn(
                'postalcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Postal code'
            )->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'City'
            )->addColumn(
                'province',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Province'
            )->addColumn(
                'telephone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Telephone'
            )->addColumn(
                'fax',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Fax'
            )->addColumn(
                'accountnumber',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Account number'
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
                'UPS Account'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    public function createPickupTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('upslabelpickup');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'upslabelaccount'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'pickup_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Pickup ID'
            )->addColumn(
                'RatePickupIndicator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1,
                ['nullable' => false, 'default' => 'N'],
                'Rate Pickup Indicator'
            )->addColumn(
                'CloseTime',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Close Time'
            )->addColumn(
                'ReadyTime',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Ready Time'
            )->addColumn(
                'PickupDateYear',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                4,
                ['nullable' => false, 'default' => ''],
                'Pickup Date Year'
            )->addColumn(
                'PickupDateMonth',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => false, 'default' => ''],
                'Pickup Date Month'
            )->addColumn(
                'PickupDateDay',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => false, 'default' => ''],
                'Pickup Date Day'
            )->addColumn(
                'AlternateAddressIndicator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1,
                ['nullable' => false, 'default' => 'N'],
                'Alternate Address Indicator'
            )->addColumn(
                'ServiceCode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => false, 'default' => ''],
                'Service Code'
            )->addColumn(
                'Quantity',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Quantity'
            )->addColumn(
                'DestinationCountryCode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => false, 'default' => ''],
                'Destination Country Code'
            )->addColumn(
                'ContainerCode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Container Code'
            )->addColumn(
                'Weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'Weight'
            )->addColumn(
                'UnitOfMeasurement',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => false, 'default' => ''],
                'Unit Of Measurement'
            )->addColumn(
                'OverweightIndicator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1,
                ['nullable' => false, 'default' => 'N'],
                'Overweight Indicator'
            )->addColumn(
                'PaymentMethod',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => false, 'default' => ''],
                'Payment Method'
            )->addColumn(
                'SpecialInstruction',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Special Instruction'
            )->addColumn(
                'ReferenceNumber',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Reference Number'
            )->addColumn(
                'Notification',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'Notification'
            )->addColumn(
                'ConfirmationEmailAddress',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Confirmation Email Address'
            )->addColumn(
                'UndeliverableEmailAddress',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Undeliverable Email Address'
            )->addColumn(
                'ShipFrom',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Ship From'
            )->addColumn(
                'pickup_request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'pickup request'
            )->addColumn(
                'pickup_response',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'pickup response'
            )->addColumn(
                'pickup_cancel',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'pickup cancel'
            )->addColumn(
                'pickup_cancel_request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'pickup cancel_request'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'status'
            )->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '0'],
                'price'
            )->addColumn(
                'store',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => 1],
                'store'
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
                'UPS Pickup'
            );
            $setup->getConnection()->createTable($table);
        }
    }
}
