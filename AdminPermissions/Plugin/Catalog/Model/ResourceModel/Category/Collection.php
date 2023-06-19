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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Model\ResourceModel\Category;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Helper\Data;

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
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Category\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        //hide all grid columns when user haven't permission to view customer
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::category_view')) {
            $collection->getSelect()->where('0=1');

            return [$printQuery, $logQuery];
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return [$printQuery, $logQuery];
        }

        $this->helperData->filterCollection($adminPermission, $collection, 'category', 'entity_id');

        return [$printQuery, $logQuery];
    }
}
