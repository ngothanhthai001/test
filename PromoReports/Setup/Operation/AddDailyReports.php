<?php

namespace Amasty\PromoReports\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddDailyReports
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createDailyReportsTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createDailyReportsTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'amasty_ampromo_daily_reports'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_ampromo_daily_reports'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => 0]
            )->addColumn(
                'period',
                Table::TYPE_DATE,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'total_sales_with_promo',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'orders_count_with_promo',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'average_total_with_promo',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'average_total_without_promo',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                'items_per_order',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000']
            );

        $setup->getConnection()->createTable($table);
    }
}
