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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions;

/**
 * Class AbstractRole
 * @package Mageplaza\AdminPermissions\Observer\Role
 */
class AbstractRole implements ObserverInterface
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
     * @var AdminPermissions
     */
    protected $adminPermissions;

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
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        /** @var View $controller */
        $controller = $observer->getEvent()->getControllerAction();
        if ($request->isAjax()) {
            return;
        }
        $roleId = $request->getParam('rid') ?: $request->getParam('role_id');

        if (!$roleId) {
            if ($this->helperData->isAllow('Mageplaza_AdminPermissions::role_create')) {
                return;
            }

            $this->helperData->forwardToDeniedPage($controller, $request);
        }
        $this->helperData->checkForward(
            [$this->adminResource, $roleId, 'user_role', $controller, $request]
        );
    }

    /**
     * @return AdminPermissions
     */
    protected function getAdminPermission()
    {
        if (!$this->adminPermissions) {
            $this->adminPermissions = $this->helperData->getAdminPermission();
        }

        return $this->adminPermissions;
    }
}
