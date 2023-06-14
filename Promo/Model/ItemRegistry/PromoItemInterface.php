<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\ItemRegistry;

interface PromoItemInterface
{
    public const SKU = 'sku';
    public const ALLOWED_QTY = 'allowed_qty';
    public const RESERVED_QTY = 'reserved_qty';
    public const MINIMAL_PRICE = 'minimal_price';
    public const IS_ITEM_DELETED = 'is_item_deleted';
    public const DISCOUNT_ITEM = 'discount_item';
    public const DISCOUNT_AMOUNT = 'discount_amount';
    public const RULE_ID = 'rule_id';
    public const RULE_TYPE = 'rule_type';
    public const AUTO_ADD = 'auto_add';
    public const QUOTE_ID = 'quote_id';

    public function getSku();
    public function getAllowedQty();
    public function getMinimalPrice();
    public function getDiscountItem();
    public function getDiscountAmount();
    public function getRuleId();
    public function getRuleType();
    public function setSku($sku);
    public function setAllowedQty($qty);
    public function setMinimalPrice($minimalPrice);
    public function setDiscountItem($discountItem);
    public function setDiscountAmount($discountAmount);
    public function setRuleId($ruleId);
    public function setRuleType($ruleType);
    public function getQuoteId(): int;
    public function setQuoteId(int $quoteId): void;
}
