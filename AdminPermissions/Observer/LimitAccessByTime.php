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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class LimitAccessByTime
 * @package Mageplaza\AdminPermissions\Observer
 */
class LimitAccessByTime implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * LimitAccessByTime constructor.
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
     * @return ResponseInterface|void
     */
    public function execute(Observer $observer)
    {
        $adminPermission = $this->helperData->getAdminPermission();
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        /** @var View $controller */
        $controller = $observer->getEvent()->getControllerAction();
        if (!$this->helperData->isPermissionEnabled()
            || $request->getFullActionName() === 'adminhtml_auth_logout'
        ) {
            return;
        }
        if (!$this->helperData->verifyTime($adminPermission)) {
            $this->helperData->forwardToDeniedPage($controller, $request);
        }
        if (!$adminPermission->getMpCustomEnabled()) {
            return;
        }
        $customData = $adminPermission->getMpCustomLimit()
            ? Data::jsonDecode($adminPermission->getMpCustomLimit())
            : [];
        if (!empty($customData)) {
            foreach ($customData as $datum) {
                if (!$datum['status'] || $datum['type'] !== 'controller') {
                    continue;
                }

                if ($datum['class'] === trim(rtrim(get_class($controller), 'Interceptor'), '\\')) {
                    $this->helperData->forwardToDeniedPage($controller, $request);
                }
            }
        }
    }
}
