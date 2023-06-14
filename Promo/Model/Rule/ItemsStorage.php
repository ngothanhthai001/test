<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\Rule;

use Amasty\Promo\Helper\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class ItemsStorage
{
    /**
     * @var Item
     */
    private $promoItemHelper;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @var array
     */
    private $validItems = [];

    public function __construct(
        Item $promoItemHelper
    ) {
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * @param AbstractItem $item
     * @param int $ruleId
     * @return AbstractItem[]
     */
    public function getItems(AbstractItem $item, int $ruleId): array
    {
        if (!isset($this->storage[$ruleId])) {
            $this->storage[$ruleId] = $item->getQuote()->getAllVisibleItems();
        }

        return (array)$this->storage[$ruleId];
    }

    /**
     * @deprecated Item may not have ID on quote creation, so don't use item IDs. use getValidItemsForRule
     * @param Rule $rule
     * @param AbstractItem[] $items
     * @return int[]
     */
    public function getValidItemIdsForRule(Rule $rule, array $items): array
    {
        $ids = [];
        foreach ($this->getValidItemsForRule($rule, $items) as $item) {
            $ids[] = (int) $item->getId();
        }

        return $ids;
    }

    /**
     * Return passed rule validation items.
     *
     * Note: Item may not have ID on quote creation, so don't use item IDs.
     *
     * @param Rule $rule
     * @param AbstractItem[] $items
     * @return AbstractItem[]
     */
    public function getValidItemsForRule(Rule $rule, array $items): array
    {
        $ruleId = $rule->getRuleId();

        if (!isset($this->validItems[$ruleId])) {
            $validItems = [];

            foreach ($items as $item) {
                if (!$item || $this->promoItemHelper->isPromoItem($item) || $item->getProduct()->getParentProductId()) {
                    continue;
                }

                if (!$rule->getActions()->validate($item)) {
                    // if condition not valid for Parent, but valid for child then collect qty of child
                    foreach ((array)$item->getChildren() as $child) {
                        if ($rule->getActions()->validate($child)) {
                            $validItems[] = $item;
                        }
                    }
                } else {
                    $validItems[] = $item;
                }
            }

            $this->validItems = [$ruleId => $validItems];
        }

        return $this->validItems[$ruleId];
    }

    public function reset(): void
    {
        $this->storage = [];
        $this->validItems = [];
    }
}
