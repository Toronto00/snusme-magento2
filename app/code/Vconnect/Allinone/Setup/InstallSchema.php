<?php
/* 
 * The MIT License
 *
 * Copyright 2016 vConnect.dk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @category Magento
 * @package Vconnect_AllInOne
 * @author vConnect
 * @email kontakt@vconnect.dk
 * @class Vconnect_AllInOne_Helper_Data
 */
 
namespace Vconnect\Allinone\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $salesOrderTable = $installer->getTable('sales_order');
        $columns = [
            'vconnect_postnord_data' => [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => null,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Vconnect Postnord Data',
            ]
        ];
        foreach ($columns as $name => $definition) {
            $installer->getConnection()->addColumn($salesOrderTable, $name, $definition);
        }

        $quoteTable = $installer->getTable('quote');
        $columns = [
            'vconnect_postnord_data' => [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => null,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Vconnect Postnord Data',
            ]
        ];
        foreach ($columns as $name => $definition) {
            $installer->getConnection()->addColumn($quoteTable, $name, $definition);
        }

        $quoteShippingRateTable = $installer->getTable('quote_shipping_rate');
        $columns = [
            'vc_method_data' => [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => null,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Postnord Data',
            ]
        ];
        foreach ($columns as $name => $definition) {
            $installer->getConnection()->addColumn($quoteShippingRateTable, $name, $definition);
        }

        $installer->endSetup();
    }
}
