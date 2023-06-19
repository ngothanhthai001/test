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

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class DeleteModel
 * @package Mageplaza\AdminPermissions\Observer\Product
 */
class DeleteModel extends AbstractProduct
{
    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $adminPermission = $this->helperData->getAdminPermission();
        if (!$this->helperData->isEnabled() || !$adminPermission->getId()) {
            return;
        }
        /** @var Product $product */
        $product   = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        if (!$productId) {
            return;
        }

        $productName = $product->getResource()->getAttributeRawValue(
            $productId,
            'name',
            $product->getStoreId()
        );
        $allowAction = $this->helperData->isAllow('Mageplaza_AdminPermissions::product_delete');
        if (!$allowAction) {
            throw new LocalizedException(__('You don\'t have permission to delete %1 product', $productName));
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
                    throw new LocalizedException(__('You don\'t have permission to delete %1 product', $productName));
                }
                break;
            case Restriction::DENY:
                if ($productIds === Data::ALL_PRODUCT || in_array($productId, $productIds, true)) {
                    throw new LocalizedException(__('You don\'t have permission to delete %1 product', $productName));
                }
                break;
        }
    }
}
