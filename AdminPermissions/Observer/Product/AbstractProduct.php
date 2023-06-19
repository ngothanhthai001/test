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

namespace Mageplaza\AdminPermissions\Observer\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class AbstractProduct
 * @package Mageplaza\AdminPermissions\Observer\Product
 */
class AbstractProduct implements ObserverInterface
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
     * Delete constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
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

        $productId = $request->getParam('id');

        if (!$productId) {
            return;
        }
        $allowAction = $this->helperData->isAllow($this->adminResource);

        if (!$allowAction) {
            $this->helperData->forwardToDeniedPage($controller, $request);
        }
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $restriction = $adminPermission->getMpProductRestriction();
        $productIds  = $this->getProductIds($adminPermission);

        if ($productIds === null) {
            return;
        }
        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if ($productIds === Data::ALL_PRODUCT) {
                    return;
                }
                if (!in_array($productId, $productIds, true)) {
                    $this->helperData->forwardToDeniedPage($controller, $request);
                }
                break;
            case Restriction::DENY:
                if ($productIds === Data::ALL_PRODUCT || in_array($productId, $productIds, true)) {
                    $this->helperData->forwardToDeniedPage($controller, $request);
                }
                break;
        }
    }

    /**
     * @param AdminPermissions $adminPermission
     *
     * @return array|string|null
     */
    protected function getProductIds($adminPermission)
    {
        return $this->helperData->getProductIds($adminPermission);
    }
}
