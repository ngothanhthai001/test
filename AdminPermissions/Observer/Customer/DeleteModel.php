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

namespace Mageplaza\AdminPermissions\Observer\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class DeleteModel
 * @package Mageplaza\AdminPermissions\Observer\Customer
 */
class DeleteModel extends AbstractCustomer
{
    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$this->helperData->isEnabled() || !$adminPermission->getId()) {
            return;
        }
        /** @var Customer $customer */
        $customer   = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        if (!$customerId) {
            return;
        }
        $customerIds = array_filter(explode(',', $adminPermission->getMpCustomerIds()));
        $allowAction = $this->helperData->isAllow('Magento_Customer::delete');

        if (!$allowAction) {
            throw new LocalizedException(__('You don\'t have permission to delete %1 customer', $customer->getName()));
        }
        $restriction = $adminPermission->getMpCustomerRestriction();

        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($customerId, $customerIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to delete %1 customer',
                        $customer->getName()
                    ));
                }
                break;
            case Restriction::DENY:
                if (in_array($customerId, $customerIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to delete %1 customer',
                        $customer->getName()
                    ));
                }
                break;
        }
    }
}
