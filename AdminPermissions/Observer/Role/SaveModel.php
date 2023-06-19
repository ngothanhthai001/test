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

namespace Mageplaza\AdminPermissions\Observer\Role;

use Magento\Authorization\Model\Role;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class SaveModel
 * @package Mageplaza\AdminPermissions\Observer\Role
 */
class SaveModel extends AbstractRole
{
    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }

        /** @var Role $role */
        $role   = $observer->getEvent()->getObject();
        $roleId = $role->getId();
        if (!$role->getId() && !$this->helperData->isAllow('Mageplaza_AdminPermissions::role_create')) {
            throw new LocalizedException(__('You don\'t have permission to create role'));
        }
        if (!$role->getId()) {
            return;
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $allowAction = $this->helperData->isAllow('Mageplaza_AdminPermissions::role_edit');
        if (!$allowAction) {
            throw new LocalizedException(__('You don\'t have permission to edit %1 role', $role->getRoleName()));
        }
        $restriction = $adminPermission->getMpUserRoleRestriction();
        $roleIds     = array_filter(explode(',', $adminPermission->getMpUserRoleIds()));
        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($roleId, $roleIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 role',
                        $role->getRoleName()
                    ));
                }
                break;
            case Restriction::DENY:
                if (in_array($roleId, $roleIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 role',
                        $role->getRoleName()
                    ));
                }
                break;
        }
    }
}
