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

namespace Mageplaza\AdminPermissions\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Custom
 * @package Mageplaza\AdminPermissions\Model\ResourceModel
 */
class Custom extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_admin_permissions_custom', 'id');
    }

    /**
     * @return array
     */
    public function getAllClass()
    {
        try {
            $adapter = $this->getConnection();
            $select  = $adapter->select()->from(
                $this->getMainTable(),
                'class'
            );

            return $adapter->fetchCol($select);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);

            return [];
        }
    }
}
