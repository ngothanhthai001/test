<?php

namespace Amasty\PromoReports\Model;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Amasty\PromoReports\Block\Adminhtml\Reports\Filters;
use Amasty\PromoReports\Controller\Adminhtml\Reports\Ajax;
use Amasty\PromoReports\Model\Config\Source\DateRange;
use Amasty\PromoReports\Model\ResourceModel\Report;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Statistics data provider using design pattern 'Builder'
 */
class ReportBuilder
{
    /**
     * @var ReportFactory
     */
    private $reportFactory;

    /**
     * @var Report
     */
    private $reportResource;

    /**
     * @var DateProxy
     */
    private $date;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var int|string|null
     */
    private $storeId;

    /**
     * @var int|string|null
     */
    private $customerGroupId;

    /**
     * @var string|int|\DateTime|array|null
     */
    private $dateFrom;

    /**
     * @var string|int|\DateTime|array|null
     */
    private $dateTo;

    public function __construct(
        ReportFactory $reportFactory,
        Report $reportResource,
        DateProxy $date,
        Data $priceHelper
    ) {
        $this->reportFactory = $reportFactory;
        $this->reportResource = $reportResource;
        $this->date = $date;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param string|null $reportKey
     * @return \Amasty\PromoReports\Model\Report
     */
    public function build(string $reportKey = null)
    {
        switch ($reportKey) {
            case Ajax::DIGITAL_KEY:
                $data = $this->getDigitalStatistics();
                break;
            case Ajax::GRAPHIC_KEY:
                $data = $this->getGraphStatistics();
                break;
            default:
                $data = $this->getDigitalStatistics();
                $data += $this->getGraphStatistics();
        }

        return $this->reportFactory->create(['data' => $data]);
    }

    /**
     * @param int|string|null $storeId
     * @return $this
     */
    public function addStoreId($storeId = null)
    {
        if ($storeId === Filters::ALL) {
            $storeId = null;
        }

        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @param int|string|null $customerGroupId
     * @return $this
     */
    public function addCustomerGroupId($customerGroupId = null)
    {
        if ($customerGroupId === Filters::ALL) {
            $customerGroupId = null;
        }

        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @param string|null $dateRange
     * @param string|int|\DateTime|array|null $dateFrom
     * @param string|int|\DateTime|array|null $dateTo
     * @return $this
     */
    public function addDate($dateRange = null, $dateFrom = null, $dateTo = null)
    {
        if ($dateRange === DateRange::OVERALL) {
            $dateTo = null;
            $dateFrom = null;
        } elseif (!$dateRange == DateRange::CUSTOM) {
            $dateTo = $this->date->getDateWithOffsetByDays(1);
            $dateFrom = $this->date->getDateWithOffsetByDays((-1) * ($dateRange - 1));
        } else {
            $dateFrom = $this->date->date(null, $dateFrom);
            $dateTo = $this->date->date(null, $dateTo . '+1 day');
        }

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * @return array
     */
    public function getDigitalStatistics()
    {
        $conditions = $this->reportResource->loadStatisticsConditions(
            $this->storeId,
            $this->customerGroupId,
            $this->dateFrom,
            $this->dateTo
        );

        $data = $this->reportResource->getTotalDataBySelect($conditions);

        $data[ReportInterface::AVG_WITH_PROMO]
            = $this->priceHelper->currency($data[ReportInterface::AVG_WITH_PROMO], true, false);
        $data[ReportInterface::TOTAL_SALES]
            = $this->priceHelper->currency($data[ReportInterface::TOTAL_SALES], true, false);

        return $data;
    }

    /**
     * @return array
     */
    public function getGraphStatistics()
    {
        return $this->reportResource->loadAverageCartData(
            $this->storeId,
            $this->customerGroupId,
            $this->dateFrom,
            $this->dateTo
        );
    }
}
