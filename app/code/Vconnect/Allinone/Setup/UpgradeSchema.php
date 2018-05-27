<?php

namespace Vconnect\Allinone\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            /**
             * Creating table vconnect_allinone_quote_shipping_rate
             */
           $vconnectAllinoneQuoteShippingRateTableName = 'vconnect_allinone_quote_shipping_rate';

           if (!$installer->getConnection()->isTableExists($vconnectAllinoneQuoteShippingRateTableName)) {
               $vconnectAllinoneQuoteShippingRateTable = $installer->getConnection()
                   ->newTable($installer->getTable($vconnectAllinoneQuoteShippingRateTableName))
                   ->addColumn(
                       'vconnect_allinone_quote_shipping_rate_id',
                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                       null,
                       [
                           'identity'  => true,
                           'unsigned'  => true,
                           'nullable'  => false,
                           'primary'   => true,
                       ],
                       'Allione Quote Shipping Rate ID'
                   )
                   ->addColumn(
                       'quote_id',
                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                       null,
                       [
                           'nullable'  => false,
                           'default'   => 0,
                       ],
                       'Quote ID'
                   )
                   ->addColumn(
                       'address_id',
                       \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                       15,
                       [
                           'nullable'  => false,
                           'default'   => 0,
                       ],
                       'Address ID'
                   )
                   ->addColumn(
                       'rate_data',
                       \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                       null,
                       [
                           'nullable'  => false,
                       ],
                       'Shipment data'
                   )
                   ->addColumn(
                       'date_created',
                       \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                       null,
                       [
                           'nullable'  => false
                       ],
                       'Date created'
                   )
                   ->addIndex(
                       $installer->getIdxName(
                           $vconnectAllinoneQuoteShippingRateTableName,
                           ['quote_id'],
                           \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                       ),
                       ['quote_id'],
                       ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                   )
                   ->addIndex(
                       $installer->getIdxName(
                           $vconnectAllinoneQuoteShippingRateTableName,
                           ['address_id'],
                           \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                       ),
                       ['address_id'],
                       ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                   )
                   ->setComment('Vconnect Allinone Quote Shipping Rate Table');

                $installer->getConnection()->createTable($vconnectAllinoneQuoteShippingRateTable);
           }
        }

        $installer->endSetup();
    }
}
