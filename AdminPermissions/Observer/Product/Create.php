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

/**
 * Class Create
 * @package Mageplaza\AdminPermissions\Observer\Product
 */
class Create implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Create constructor.
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
        /** @var RequestInterface $request */

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::product_create')) {
            $this->helperData->forwardToDeniedPage($controller, $request);
        }
    }
}
