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

namespace Mageplaza\AdminPermissions\Model\ResourceModel\User;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\User\Model\ResourceModel\User;

/**
 * Admin user collection
 *
 * Copy of \Magento\User\Model\ResourceModel\User\Collection to fix bug with ee232
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\User\Model\User::class, User::class);
    }

    /**
     * Collection Init Select
     *
     * @since 101.1.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['user_role' => $this->getTable('authorization_role')],
            'main_table.user_id = user_role.user_id AND user_role.parent_id != 0',
            []
        )->joinLeft(
            ['detail_role' => $this->getTable('authorization_role')],
            'user_role.parent_id = detail_role.role_id',
            ['role_name']
        );
    }
}
