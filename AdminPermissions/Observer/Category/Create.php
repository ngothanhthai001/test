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
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Create
 * @package Mageplaza\AdminPermissions\Observer\Category
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
        $adminPermissions = $this->helperData->getAdminPermission();
        if (!$this->helperData->isEnabled() || !$adminPermissions->getId()) {
            return;
        }
        /** @var RequestInterface $request */
        $options = $observer->getEvent()->getOptions();
        $options->setIsAllow($this->helperData->isAllow('Mageplaza_AdminPermissions::category_create'));
    }
}
