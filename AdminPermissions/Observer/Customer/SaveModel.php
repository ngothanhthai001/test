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
 * Class SaveModel
 * @package Mageplaza\AdminPermissions\Observer\Customer
 */
class SaveModel extends AbstractCustomer
{
    /**
     * @var string
     */
    protected $adminResource = 'Mageplaza_AdminPermissions::customer_edit';

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
        /** @var Customer $customer */
        $customer   = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();

        // customer save twice (save address) when create new customer
        $isObjectNew = $this->registry->registry('mp_ap_customer_is_new');
        if (!$customerId || $isObjectNew) {
            if (!$isObjectNew) {
                $this->registry->register('mp_ap_customer_is_new', true);
            }
            if ($this->helperData->isAllow('Mageplaza_AdminPermissions::customer_create')) {
                return;
            }

            throw new LocalizedException(__('You don\'t have permission to create new customer'));
        }

        $allowAction = $this->helperData->isAllow($this->adminResource);

        if (!$allowAction) {
            throw new LocalizedException(__('You don\'t have permission to edit %1 customer', $customer->getName()));
        }
        $adminPermission = $this->helperData->getAdminPermission();
        $customerIds     = array_filter(explode(',', $adminPermission->getMpCustomerIds()));
        $restriction     = $adminPermission->getMpCustomerRestriction();

        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($customerId, $customerIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 customer',
                        $customer->getName()
                    ));
                }
                break;
            case Restriction::DENY:
                if (in_array($customerId, $customerIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 customer',
                        $customer->getName()
                    ));
                }
                break;
        }
    }
}
