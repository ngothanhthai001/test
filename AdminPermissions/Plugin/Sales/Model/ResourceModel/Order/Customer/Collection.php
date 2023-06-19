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

namespace Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel\Order\Customer;

use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions;

/**
 * Class Collection
 * @package Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel\Order\Customer
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
     * @param \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     */
    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        //hide all grid columns when user haven't permission to view product
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::customer_view')) {
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
     * @param \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection
     */
    private function filterCollection($adminPermission, $collection)
    {
        $this->helperData->filterCollection($adminPermission, $collection, 'customer', 'entity_id');
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection
     */
    public function beforeGetSelectCountSql(\Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection)
    {
        if ($this->helperData->isAllow('Mageplaza_AdminPermissions::customer_view')) {
            $adminPermission = $this->helperData->getAdminPermission();
            if ($adminPermission->getId()) {
                $this->filterCollection($adminPermission, $collection);
            }
        } else {
            $collection->getSelect()->where('0=1');
        }
    }
}
