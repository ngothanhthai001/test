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

namespace Mageplaza\AdminPermissions\Observer\Custom;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Delete
 * @package Mageplaza\AdminPermissions\Observer\Custom
 */
class Delete implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $adminResource = '';

    /**
     * AbstractCustomer constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$this->helperData->isPermissionEnabled() || !$adminPermission->getMpCustomEnabled()) {
            return;
        }
        /** @var AbstractModel $object */
        $object = $observer->getEvent()->getObject();

        $customData = $adminPermission->getMpCustomLimit()
            ? Data::jsonDecode($adminPermission->getMpCustomLimit())
            : [];
        if (!empty($customData)) {
            foreach ($customData as $datum) {
                if (!$datum['status'] || $datum['type'] !== 'model' || $datum['action'] !== 'delete') {
                    continue;
                }
                if ($datum['class'] === trim(rtrim(get_class($object), 'Interceptor'), '\\')) {
                    throw new LocalizedException(__('You don\'t have permission to delete model'));
                }
            }
        }
    }
}
