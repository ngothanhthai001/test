<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\ItemRegistry;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Model\ResourceModel\Rule;
use Amasty\Promo\Model\RuleData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class PromoItemRemover
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var RuleData
     */
    private $ruleData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Rule $rule,
        LoggerInterface $logger,
        RuleData $ruleData
    ) {
        $this->rule = $rule;
        $this->logger = $logger;
        $this->ruleData = $ruleData;
    }

    /**
     * @param PromoItemData[] $items
     * @return PromoItemData[]
     */
    public function execute(array $items): array
    {
        $ruleIds = [];
        $allSkus = [];
        $availableSkus = [];
        $sameProductSkus = [];

        foreach ($items as $key => $item) {
            if (!in_array($item->getRuleId(), $ruleIds)) {
                $ruleIds[] = $item->getRuleId();
            }

            if (!in_array($item->getSku(), $allSkus)) {
                $allSkus[$key] = $item->getSku();
            }

            try {
                // Rule with action == SAME_PRODUCT doesn't have setting 'Promo Skus'.
                // So we don't need to remove items for such rule.
                $rule = $this->ruleData->getRuleByLinkId((string)$item->getRuleId());
                if ($rule->getSimpleAction() === GiftRuleInterface::SAME_PRODUCT) {
                    $sameProductSkus[] = $item->getSku();
                }
            } catch (NoSuchEntityException|LocalizedException $e) {
                $this->logger->critical($e);
            }
        }

        $ruleSkus = $this->rule->isApplicable($ruleIds, 'sku');

        foreach ($ruleSkus as $skus) {
            $availableSkus[] = explode(',', $skus['sku']);
        }

        $availableSkus = array_merge([], ...$availableSkus);
        $availableSkus = array_map('trim', $availableSkus);

        foreach ($allSkus as $key => $sku) {
            if (!in_array($sku, $availableSkus) && !in_array($sku, $sameProductSkus)) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
