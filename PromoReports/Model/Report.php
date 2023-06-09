<?php

namespace Amasty\PromoReports\Model;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Magento\Framework\Model\AbstractModel;

class Report extends AbstractModel implements ReportInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\Report::class);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->_getData(ReportInterface::ENTITY_ID);
    }

    /**
     * @param int $entityId
     * @return ReportInterface
     */
    public function setId($entityId): ReportInterface
    {
        $this->setData(ReportInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return (int)$this->_getData(ReportInterface::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return ReportInterface
     */
    public function setStoreId(int $storeId): ReportInterface
    {
        $this->setData(ReportInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId(): int
    {
        return (int)$this->_getData(ReportInterface::CUSTOMER_GROUP_ID);
    }

    /**
     * @param int $customerGroupId
     * @return ReportInterface
     */
    public function setCustomerGroupId(int $customerGroupId): ReportInterface
    {
        $this->setData(ReportInterface::CUSTOMER_GROUP_ID, $customerGroupId);

        return $this;
    }

    /**
     * @return string
     */
    public function getPeriod(): string
    {
        return (string)$this->_getData(ReportInterface::PERIOD);
    }

    /**
     * @param string $date
     * @return ReportInterface
     */
    public function setPeriod(string $date): ReportInterface
    {
        $this->setData(ReportInterface::PERIOD, $date);

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalSales(): float
    {
        return (float)$this->_getData(ReportInterface::TOTAL_SALES);
    }

    /**
     * @param float $totalSales
     * @return ReportInterface
     */
    public function setTotalSales(float $totalSales): ReportInterface
    {
        $this->setData(ReportInterface::TOTAL_SALES, $totalSales);

        return $this;
    }

    /**
     * @return int
     */
    public function getOrdersCount(): int
    {
        return (int)$this->_getData(ReportInterface::ORDERS_COUNT);
    }

    /**
     * @param int $ordersCount
     * @return ReportInterface
     */
    public function setOrdersCount(int $ordersCount): ReportInterface
    {
        $this->setData(ReportInterface::ORDERS_COUNT, $ordersCount);

        return $this;
    }

    /**
     * @return float
     */
    public function getAverageWithPromo(): float
    {
        return (float)$this->_getData(ReportInterface::AVG_WITH_PROMO);
    }

    /**
     * @param float $averageTotal
     * @return ReportInterface
     */
    public function setAverageWithPromo(float $averageTotal): ReportInterface
    {
        $this->setData(ReportInterface::AVG_WITH_PROMO, $averageTotal);

        return $this;
    }

    /**
     * @return float
     */
    public function getAverageWithoutPromo(): float
    {
        return (float)$this->_getData(ReportInterface::AVG_WITHOUT_PROMO);
    }

    /**
     * @param float $averageTotal
     * @return ReportInterface
     */
    public function setAverageWithoutPromo(float $averageTotal): ReportInterface
    {
        $this->setData(ReportInterface::AVG_WITHOUT_PROMO, $averageTotal);

        return $this;
    }

    /**
     * @return float
     */
    public function getItemsPerOrder(): float
    {
        return (float)$this->_getData(ReportInterface::PROMO_ITEMS_PER_ORDER);
    }

    /**
     * @param float $itemsPerOrder
     * @return ReportInterface
     */
    public function setItemsPerOrder(float $itemsPerOrder): ReportInterface
    {
        $this->setData(ReportInterface::PROMO_ITEMS_PER_ORDER, $itemsPerOrder);

        return $this;
    }
}
