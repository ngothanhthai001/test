<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

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
