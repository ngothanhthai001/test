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

namespace Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class AbstractCollection
 * @package Mageplaza\AdminPermissions\Plugin\Sales\Model\ResourceModel
 */
abstract class AbstractCollection
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * AbstractCollection constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param SearchResult|Collection $collection
     * @param $printQuery
     * @param $logQuery
     *
     * @return array
     */
    protected function filterCollection($collection, $printQuery, $logQuery)
    {
        if (!$this->helperData->isPermissionEnabled()) {
            return [$printQuery, $logQuery];
        }
        $adminPermission = $this->helperData->getAdminPermission();

        $allowStoreIds = $this->helperData->getAllowedRestrictionStoreIds($adminPermission);

        if (!empty($allowStoreIds)) {
            $collection->addFieldToFilter('store_id', ['in' => $allowStoreIds]);
        }

        return [$printQuery, $logQuery];
    }
}
