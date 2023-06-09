<?php

namespace Amasty\PromoReports\Model;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Amasty\PromoReports\Api\Data\ReportViewModelInterface;
use Amasty\PromoReports\Model\ResourceModel\Report;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

/**
 * View Model Report Manager
 */
class ReportManagement implements ReportViewModelInterface
{
    /**
     * @var RuleCollection
     */
    private $salesRuleCollectionFactory;

    /**
     * @var Report
     */
    private $reportResource;

    public function __construct(
        RuleCollection $salesRuleCollectionFactory,
        Report $reportResource
    ) {
        $this->salesRuleCollectionFactory = $salesRuleCollectionFactory;
        $this->reportResource = $reportResource;
    }

    /**
     * @return int
     */
    public function getCountActiveFreeGiftRules(): int
    {
        return $this->reportResource->getCountActiveFreeGiftRules();
    }

    /**
     * @return array
     */
    public function getStatisticFields(): array
    {
        return [
            ReportInterface::TOTAL_SALES => __('Total Sales'),
            ReportInterface::ORDERS_COUNT => __('Orders'),
            ReportInterface::AVG_WITH_PROMO => __('Average Cart Total'),
            ReportInterface::PROMO_ITEMS_PER_ORDER => __('Promo Items per Order')
        ];
    }

    /**
     * @return Collection
     */
    public function getSalesRuleCollection(): Collection
    {
        return $this->salesRuleCollectionFactory->create();
    }
}
