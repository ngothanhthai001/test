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
 * @package     Mageplaza_AdminPermissions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mageplaza\Core\Helper\AbstractData;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\AdminPermissions\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var AbstractData
     */
    protected $helperData;

    /**
     * InstallSchema constructor.
     *
     * @param AbstractData $helperData
     */
    public function __construct(AbstractData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        if (!$this->helperData->versionCompare('2.3.0')) {
            if (!$setup->tableExists('mageplaza_admin_permissions')) {
                $table = $connection->newTable($setup->getTable('mageplaza_admin_permissions'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true
                    ])
                    ->addColumn('role_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false])
                    ->addColumn('mp_store_ids', Table::TYPE_TEXT, 64)
                    ->addColumn('mp_sales_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_category_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_category_ids', Table::TYPE_TEXT, '64k')
                    ->addColumn('mp_product_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_product_apply_for', Table::TYPE_TEXT, 30)
                    ->addColumn('mp_product_ids', Table::TYPE_TEXT, '64k')
                    ->addColumn('mp_customer_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_customer_ids', Table::TYPE_TEXT, '64k')
                    ->addColumn('mp_prodattr_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_prodattr_ids', Table::TYPE_TEXT, '64k')
                    ->addColumn('mp_user_role_restriction', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_user_role_ids', Table::TYPE_TEXT, '64k')
                    ->addColumn('mp_custom_enabled', Table::TYPE_INTEGER, 1)
                    ->addColumn('mp_custom_limit', Table::TYPE_TEXT, '2M')
                    ->addColumn('mp_period_days', Table::TYPE_TEXT, 60)
                    ->addColumn('mp_limit_type', Table::TYPE_INTEGER, 1)
                    ->addColumn('mp_period_from', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_period_to', Table::TYPE_TEXT, 10)
                    ->addColumn('mp_period_from_date', Table::TYPE_TIMESTAMP, 10)
                    ->addColumn('mp_period_to_date', Table::TYPE_TIMESTAMP, 10)
                    ->addIndex($setup->getIdxName('mageplaza_admin_permissions', ['role_id']), ['role_id'])
                    ->addForeignKey(
                        $setup->getFkName('mageplaza_admin_permissions', 'role_id', 'authorization_role', 'role_id'),
                        'role_id',
                        $setup->getTable('authorization_role'),
                        'role_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('Mageplaza Admin Permissions');

                $connection->createTable($table);
            }

            if (!$setup->tableExists('mageplaza_admin_permissions_custom')) {
                $table = $connection->newTable($setup->getTable('mageplaza_admin_permissions_custom'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true
                    ])
                    ->addColumn('type', Table::TYPE_TEXT, 20)
                    ->addColumn('class', Table::TYPE_TEXT, '64k')
                    ->setComment('Mageplaza Admin Permissions Customize');

                $connection->createTable($table);
            }
        }

        $setup->endSetup();
    }
}
