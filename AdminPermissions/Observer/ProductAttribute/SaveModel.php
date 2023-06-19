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

namespace Mageplaza\AdminPermissions\Observer\ProductAttribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class SaveModel
 * @package Mageplaza\AdminPermissions\Observer\ProductAttribute
 */
class SaveModel extends AbstractAttribute
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
        /** @var Attribute $attribute */
        $attribute   = $observer->getEvent()->getAttribute();
        $attributeId = $attribute->getId();
        if (!$attributeId
            && !$this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_create')
        ) {
            throw new LocalizedException(__('You don\'t have permission to create attribute'));
        }
        if (!$attributeId) {
            return;
        }
        $allowAction = $this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_edit');
        if (!$allowAction && $this->helperData->getRequest()->getFullActionName() !== 'catalog_product_save') {
            throw new LocalizedException(__('You don\'t have permission to edit %1 attribute', $attribute->getName()));
        }
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $restriction  = $adminPermission->getMpProdattrRestriction();
        $attributeIds = array_filter(explode(',', $adminPermission->getMpProdattrIds()));
        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($attributeId, $attributeIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 attribute',
                        $attribute->getName()
                    ));
                }
                break;
            case Restriction::DENY:
                if (in_array($attributeId, $attributeIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 attribute',
                        $attribute->getName()
                    ));
                }
                break;
        }
    }
}
