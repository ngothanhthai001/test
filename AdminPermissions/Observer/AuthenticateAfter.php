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

namespace Mageplaza\AdminPermissions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Model\User;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class AuthenticateAfter
 * @package Mageplaza\AdminPermissions\Observer
 */
class AuthenticateAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * AuthenticateAfter constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @throws AuthenticationException
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getEvent()->getResult();

        /** @var User $user */
        $user = $observer->getEvent()->getUser();
        $periodDays = '';
        $adminPermissions = $this->helperData->getAdminPermission($user->getRole()->getId());
        if(!empty($adminPermissions->getMpPeriodDays())){
            $periodDays       = array_filter(explode(',', $adminPermissions->getMpPeriodDays()));
        }

        if (empty($periodDays)
            || !$result
            || !$this->helperData->isEnabled()
            || !$adminPermissions->getId()
            || !$adminPermissions->getMpEnabled()
        ) {
            return;
        }

        if (!$this->helperData->verifyTime($adminPermissions)) {
            throw new AuthenticationException(__('You need more permissions to access in this time.'));
        }
    }
}
