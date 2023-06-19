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

namespace Mageplaza\AdminPermissions\Plugin\Sales\Block\Adminhtml\Order\Create\Store;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Select
 * @package Mageplaza\AdminPermissions\Plugin\Sales\Block\Adminhtml\Order\Create\Store
 */
class Select
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * Select constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Store\Select $subject
     * @param mixed ...$arg
     *
     * @return array
     */
    public function beforeGetStoreCollection(
        \Magento\Sales\Block\Adminhtml\Order\Create\Store\Select $subject,
        ...$arg
    ) {
        if (!$this->helperData->isPermissionEnabled()) {
            return $arg;
        }

        $adminPermission = $this->helperData->getAdminPermission();
        $allowStoreIds   = $this->helperData->getAllowedRestrictionStoreIds($adminPermission);

        if (!empty($allowStoreIds)) {
            $subject->setStoreIds($allowStoreIds);
        }

        return $arg;
    }
}
