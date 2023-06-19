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

namespace Mageplaza\AdminPermissions\Observer\Category;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class AbstractCategory
 * @package Mageplaza\AdminPermissions\Observer\Category
 */
class AbstractCategory implements ObserverInterface
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

        $categoryId     = $this->getCategoryId($request);
        $rootCategoryId = $this->helperData->getStoreManager()->getDefaultStoreView()->getRootCategoryId();
        if (!$categoryId || $rootCategoryId === $categoryId) {
            return;
        }

        $this->helperData->checkForward(
            [$this->adminResource, $categoryId, 'category', $controller, $request]
        );
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getCategoryId($request)
    {
        return (string) $request->getParam('id');
    }
}
