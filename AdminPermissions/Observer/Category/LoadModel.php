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
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class LoadModel
 * @package Mageplaza\AdminPermissions\Observer\Category
 */
class LoadModel extends AbstractCategory
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }
        $adminPermission = $this->helperData->getAdminPermission();
        /** @var Category $category */
        $category    = $observer->getEvent()->getCategory();
        $categoryId  = $category->getId();
        $restriction = $adminPermission->getMpCategoryRestriction();
        $categoryIds = null;
        if(!empty($adminPermission->getMpCategoryIds())){
            $categoryIds = array_filter(explode(',', $adminPermission->getMpCategoryIds()));
        }
        if ($this->helperData->isAllow('Mageplaza_AdminPermissions::category_edit')) {
            if (!$adminPermission->getId()) {
                return;
            }
            switch ($restriction) {
                case Restriction::NO:
                    break;
                case Restriction::ALLOW:
                    if (!in_array($categoryId, $categoryIds, true)) {
                        $category->setIsReadonly(true);
                    }
                    break;
                case Restriction::DENY:
                    if (in_array($categoryId, $categoryIds, true)) {
                        $category->setIsReadonly(true);
                    }
                    break;
            }
        } else {
            $category->setIsReadonly(true);
        }

        if ($this->helperData->isAllow('Mageplaza_AdminPermissions::category_delete')) {
            if (!$adminPermission->getId()) {
                return;
            }
            switch ($restriction) {
                case Restriction::NO:
                    break;
                case Restriction::ALLOW:
                    if (!in_array($categoryId, $categoryIds, true)) {
                        $category->setIsDeleteable(false);
                    }
                    break;
                case Restriction::DENY:
                    if (in_array($categoryId, $categoryIds, true)) {
                        $category->setIsDeleteable(false);
                    }
                    break;
            }
        } else {
            $category->setIsDeleteable(false);
        }
    }
}
