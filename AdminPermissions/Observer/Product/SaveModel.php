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
 * Class SaveModel
 * @package Mageplaza\AdminPermissions\Observer\Product
 */
class SaveModel extends AbstractProduct
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
        if (!$productId && !$this->helperData->isAllow('Mageplaza_AdminPermissions::product_create')) {
            throw new LocalizedException(__('You don\'t have permission to create product'));
        }
        if (!$productId) {
            return;
        }
        $productIds  = $this->getProductIds($adminPermission);
        $allowAction = $this->helperData->isAllow('Mageplaza_AdminPermissions::product_edit');

        if (!$allowAction) {
            throw new LocalizedException(__('You don\'t have permission to edit %1 product', $product->getName()));
        }
        $restriction = $adminPermission->getMpProductRestriction();

        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if ($productIds === Data::ALL_PRODUCT) {
                    return;
                }
                if (!in_array($productId, $productIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 product',
                        $product->getName()
                    ));
                }
                break;
            case Restriction::DENY:
                if ($productIds === Data::ALL_PRODUCT || in_array($productId, $productIds, true)) {
                    throw new LocalizedException(__(
                        'You don\'t have permission to edit %1 product',
                        $product->getName()
                    ));
                }
                break;
        }
    }
}
