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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Model\ResourceModel\Product\Attribute;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Collection
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Model\ResourceModel\Product\Attribute
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     */
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        //fix bug save product on ee
        $fullActionName = $this->helperData->getRequest()->getFullActionName();
        if ($fullActionName === 'catalog_product_save') {
            return [$printQuery, $logQuery];
        }

        //hide all grid columns when user haven't permission to view product
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_view')) {
            $collection->getSelect()->where('0=1');

            return [$printQuery, $logQuery];
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if (!$adminPermission->getId()) {
            return [$printQuery, $logQuery];
        }

        $this->helperData->filterCollection($adminPermission, $collection, 'prodattr', 'main_table.attribute_id');

        return [$printQuery, $logQuery];
    }
}
