<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

namespace Amasty\PromoReports\Api\Data;

interface ReportInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ENTITY_ID = 'id';
    public const STORE_ID = 'store_id';
    public const CUSTOMER_GROUP_ID = 'customer_group_id';
    public const PERIOD = 'period';
    public const TOTAL_SALES = 'total_sales_with_promo';
    public const ORDERS_COUNT = 'orders_count_with_promo';
    public const PROMO_ITEMS_PER_ORDER = 'items_per_order';
    public const AVG_WITH_PROMO = 'average_total_with_promo';
    public const AVG_WITHOUT_PROMO = 'average_total_without_promo';

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $entityId
     * @return ReportInterface
     */
    public function setId($entityId): ReportInterface;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return ReportInterface
     */
    public function setStoreId(int $storeId): ReportInterface;

    /**
     * @return int
     */
    public function getCustomerGroupId(): int;

    /**
     * @param int $customerGroupId
     * @return ReportInterface
     */
    public function setCustomerGroupId(int $customerGroupId): ReportInterface;

    /**
     * @return string
     */
    public function getPeriod(): string;

    /**
     * @param string $date
     * @return ReportInterface
     */
    public function setPeriod(string $date): ReportInterface;

    /**
     * @return float
     */
    public function getTotalSales(): float;

    /**
     * @param float $totalSales
     * @return ReportInterface
     */
    public function setTotalSales(float $totalSales): ReportInterface;

    /**
     * @return int
     */
    public function getOrdersCount(): int;

    /**
     * @param int $ordersCount
     * @return ReportInterface
     */
    public function setOrdersCount(int $ordersCount): ReportInterface;

    /**
     * @return float
     */
    public function getAverageWithPromo(): float;

    /**
     * @param float $averageTotal
     * @return ReportInterface
     */
    public function setAverageWithPromo(float $averageTotal): ReportInterface;

    /**
     * @return float
     */
    public function getAverageWithoutPromo(): float;

    /**
     * @param float $averageTotal
     * @return ReportInterface
     */
    public function setAverageWithoutPromo(float $averageTotal): ReportInterface;

    /**
     * @return float
     */
    public function getItemsPerOrder(): float;

    /**
     * @param float $itemsPerOrder
     * @return ReportInterface
     */
    public function setItemsPerOrder(float $itemsPerOrder): ReportInterface;
}
