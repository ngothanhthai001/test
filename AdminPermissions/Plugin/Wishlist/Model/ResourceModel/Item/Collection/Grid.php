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

namespace Mageplaza\AdminPermissions\Plugin\Wishlist\Model\ResourceModel\Item\Collection;

use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class Grid
 * @package Mageplaza\AdminPermissions\Plugin\Wishlist\Model\ResourceModel\Item\Collection
 */
class Grid
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Collection constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid $collection
     */
    public function beforeGetSelectCountSql(\Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid $collection)
    {
        if ($this->helperData->isAllow('Mageplaza_AdminPermissions::product_view')) {
            $adminPermission = $this->helperData->getAdminPermission();
            if ($adminPermission->getId()) {
                $this->filterCollection($adminPermission, $collection);
            }
        } else {
            $collection->getSelect()->where('0=1');
        }
    }

    /**
     * @param AdminPermissions $adminPermission
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid $collection
     */
    public function filterCollection($adminPermission, $collection)
    {
        $restriction = $adminPermission->getMpProductRestriction();
        $productIds  = $this->helperData->getProductIds($adminPermission);

        if ($productIds === null) {
            return;
        }
        switch ($restriction) {
            case Restriction::NO:
                break;
            case Restriction::ALLOW:
                if ($productIds === Data::ALL_PRODUCT) {
                    break;
                }
                $collection->addFieldToFilter('product_id', ['in' => $productIds]);
                break;
            case Restriction::DENY:
                if ($productIds === Data::ALL_PRODUCT) {
                    $collection->getSelect()->where('0=1');
                } else {
                    $collection->addFieldToFilter('product_id', ['nin' => $productIds]);
                }
                break;
        }
    }
}
