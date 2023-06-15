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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\ExtraFee\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $extraFeeTableName = $setup->getTable('mageplaza_extrafee_rule');
            $connection->addColumn($extraFeeTableName, 'description', [
                'type'     => Table::TYPE_TEXT,
                'length'   => '64K',
                'nullable' => true,
                'comment'  => 'Description',
                'after'    => 'status'
            ]);

        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $extraFeeTableName = $setup->getTable('mageplaza_extrafee_rule');
            $connection->addColumn($extraFeeTableName, 'allow_note_message', [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'comment'  => 'Allow Note Message',
                'after'    => 'customer_groups'
            ]);
            $connection->addColumn($extraFeeTableName, 'message_title', [
                'type'     => Table::TYPE_TEXT,
                'length'   => '64K',
                'nullable' => true,
                'comment'  => 'Message Title',
                'after'    => 'allow_note_message'
            ]);
            $connection->addColumn($extraFeeTableName, 'from_date', [
                'type'     => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment'  => 'From Date',
                'after'    => 'message_title'
            ]);
            $connection->addColumn($extraFeeTableName, 'to_date', [
                'type'     => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment'  => 'To Date',
                'after'    => 'from_date'
            ]);
        }

        $setup->endSetup();
    }
}
