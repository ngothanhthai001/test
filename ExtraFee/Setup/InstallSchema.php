<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\ExtraFee\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (!$installer->tableExists('mageplaza_extrafee_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_extrafee_rule'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ], 'Profile Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Rule Name')
                ->addColumn('status', Table::TYPE_INTEGER, 1, ['nullable' => false], 'Rule Status')
                ->addColumn('store_ids', Table::TYPE_TEXT, 255, ['nullable' => false], 'Stores')
                ->addColumn('customer_groups', Table::TYPE_TEXT, 64, ['nullable' => false], 'Customer Groups')
                ->addColumn('priority', Table::TYPE_INTEGER, 10, [], 'Priority')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Attribute Conditions')
                ->addColumn('apply_type', Table::TYPE_INTEGER, 10, [], 'Apply Type')
                ->addColumn('fee_type', Table::TYPE_INTEGER, 10, [], 'Fee Type')
                ->addColumn('amount', Table::TYPE_DECIMAL, '12,4', [], 'Amount')
                ->addColumn('area', Table::TYPE_INTEGER, 10, [], 'Area')
                ->addColumn('display_type', Table::TYPE_INTEGER, 10, [], 'Display Type')
                ->addColumn('is_required', Table::TYPE_INTEGER, 1, [], 'Is Required')
                ->addColumn('fee_tax', Table::TYPE_INTEGER, 10, [], 'Fee Tax')
                ->addColumn('sort_order', Table::TYPE_INTEGER, 10, [], 'Cart Sort Order')
                ->addColumn('refundable', Table::TYPE_INTEGER, 1, [], 'Refundable')
                ->addColumn('stop_further_processing', Table::TYPE_INTEGER, 1, [], 'Stop Further Processing')
                ->addColumn('labels', Table::TYPE_TEXT, '1M', [], 'Labels')
                ->addColumn('options', Table::TYPE_TEXT, '2M', [], 'Options')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Update At'
                )
                ->setComment('Extra Fee Rule Table');

            $connection->createTable($table);
        }

        $installer->endSetup();
    }
}
