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

namespace Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel\Transaction\Grid;

use Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel\Transaction\Grid
 */
class Collection extends AbstractCollection
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     */
    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        //hide all grid columns when user haven't permission to view transaction
        if (!$this->helperData->isAllow('Magento_Sales::transactions')
        ) {
            $collection->addFieldToFilter('store_id', ['in' => [null]]);

            return [$printQuery, $logQuery];
        }

        return $this->filterCollection($collection, $printQuery, $logQuery);
    }
}
