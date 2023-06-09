<?php

namespace Amasty\PromoReports\Api\Data;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;

interface ReportViewModelInterface extends ArgumentInterface
{
    /**
     * @return int
     */
    public function getCountActiveFreeGiftRules(): int;

    /**
     * @return array
     */
    public function getStatisticFields(): array;

    /**
     * @return Collection
     */
    public function getSalesRuleCollection(): Collection;
}
