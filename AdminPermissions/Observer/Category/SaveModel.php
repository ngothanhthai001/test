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

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class SaveModel
 * @package Mageplaza\AdminPermissions\Observer\Category
 */
class SaveModel extends AbstractCategory
{
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
        /** @var Category $product */
        $category = $observer->getEvent()->getCategory();

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::category_create') && !$category->getId()) {
            throw new LocalizedException(__('You don\'t have permission to create category'));
        }
        $categoryId = $category->getId();
        if (!$categoryId) {
            return;
        }

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::category_edit')) {
            throw new LocalizedException(__('You don\'t have permission to edit %1 category', $category->getName()));
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $restriction = $adminPermission->getMpCategoryRestriction();
        $categoryIds = array_filter(explode(',', $adminPermission->getMpCategoryIds()));
        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($categoryId, $categoryIds, true)) {
                    throw new LocalizedException(
                        __('You don\'t have permission to edit %1 category', $category->getName())
                    );
                }
                break;
            case Restriction::DENY:
                if (in_array($categoryId, $categoryIds, true)) {
                    throw new LocalizedException(
                        __('You don\'t have permission to edit %1 category', $category->getName())
                    );
                }
                break;
        }
    }
}
