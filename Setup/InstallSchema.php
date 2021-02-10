<?php

namespace Maurisource\MageShip\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('mageship_rates')
        )->addColumn(
            'rates_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )->addColumn(
            'carrier_code',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Carrier Code'
        )->addColumn(
            'carrier_title',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Carrier Name'
        )->addColumn(
            'method_name',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Method Name'
        )->addColumn(
            'method_code',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Method Code'
        )->addColumn(
            'country_code',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Country Code'
        )->addColumn(
            'post_code',
            Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'PostCode'
        )->addColumn(
            'weight_from',
            Table::TYPE_DECIMAL,
            "11,4",
            ['nullable' => false],
            'Start Weight'
        )->addColumn(
            'weight_to',
            Table::TYPE_DECIMAL,
            "11,4",
            ['nullable' => false],
            'End Weight'
        )->addColumn(
            'price',
            Table::TYPE_DECIMAL,
            "11,4",
            ['nullable' => false],
            'Price'
        )->setComment(
            'Rates for Failover'
        );

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
