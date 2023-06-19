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
 * Class DeleteModel
 * @package Mageplaza\AdminPermissions\Observer\Customer
 */
class DeleteModel extends AbstractAttribute
{
    /**
     * @var string
     */
    protected $adminResource = 'Mageplaza_AdminPermissions::product_attribute_delete';

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
        if (!$attribute->getId()) {
            return;
        }
        $allowAction = $this->helperData->isAllow($this->adminResource);
        if (!$allowAction) {
            throw new LocalizedException(__(
                'You don\'t have permission to delete %1 attribute',
                $attribute->getName()
            ));
        }
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $attributeIds = array_filter(explode(',', $adminPermission->getMpProdattrIds()));
        $restriction  = $adminPermission->getMpProdattrRestriction();

        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($attributeId, $attributeIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to delete %1 attribute',
                        $attribute->getName()
                    ));
                }
                break;
            case Restriction::DENY:
                if (in_array($attributeId, $attributeIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to delete %1 attribute',
                        $attribute->getName()
                    ));
                }
                break;
        }
    }
}
