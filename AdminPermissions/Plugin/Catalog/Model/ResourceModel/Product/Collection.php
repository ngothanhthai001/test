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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Model\ResourceModel\Product;

use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class Collection
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Model\ResourceModel\Product
 */
class Collection
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     */
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        //hide all grid columns when user haven't permission to view product
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::product_view')) {
            $collection->getSelect()->where('0=1');

            return [$printQuery, $logQuery];
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return [$printQuery, $logQuery];
        }

        $this->filterCollection($adminPermission, $collection);

        return [$printQuery, $logQuery];
    }

    /**
     * @param AdminPermissions $adminPermission
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
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
                $collection->addIdFilter($productIds);
                break;
            case Restriction::DENY:
                if ($productIds === Data::ALL_PRODUCT) {
                    $collection->getSelect()->where('0=1');
                } else {
                    $collection->addIdFilter($productIds, true);
                }
                break;
        }
    }
}
