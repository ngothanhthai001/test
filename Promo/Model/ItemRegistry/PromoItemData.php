<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\ItemRegistry;

use Amasty\Promo\Model\ResourceModel\PromoItem;
use Magento\Framework\Model\AbstractModel;

/**
 * Promotion Item Data - record of item added by Sales Rule
 */
class PromoItemData extends AbstractModel implements PromoItemInterface
{
    protected function _construct()
    {
        $this->_init(PromoItem::class);
    }

    /**
     * @return array
     */
    public function getDiscountArray()
    {
        return [
            self::MINIMAL_PRICE => $this->getMinimalPrice(),
            self::DISCOUNT_ITEM => $this->getDiscountItem()
        ];
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->_getData(self::SKU);
    }

    /**
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);

        return $this;
    }

    /**
     * Quantity assigned by rule
     *
     * @return int
     */
    public function getAllowedQty()
    {
        return $this->_getData(self::ALLOWED_QTY);
    }

    /**
     * @param int $allowedQty
     *
     * @return $this
     */
    public function setAllowedQty($allowedQty)
    {
        $this->setData(self::ALLOWED_QTY, $allowedQty);

        return $this;
    }

    /**
     * Get allowed quantity of gift for add to cart
     *
     * @return int
     */
    public function getQtyToProcess()
    {
        return $this->getAllowedQty() - $this->getReservedQty();
    }

    /**
     * Quantity of current gift in the cart
     *
     * @return int
     */
    public function getReservedQty()
    {
        return $this->_getData(self::RESERVED_QTY);
    }

    /**
     * @param int $reservedQty
     *
     * @return $this
     */
    public function setReservedQty($reservedQty)
    {
        $this->setData(self::RESERVED_QTY, $reservedQty);

        return $this;
    }

    /**
     * @return float
     */
    public function getMinimalPrice()
    {
        return $this->_getData(self::MINIMAL_PRICE);
    }

    /**
     * @param float $minimalPrice
     *
     * @return $this
     */
    public function setMinimalPrice($minimalPrice)
    {
        $this->setData(self::MINIMAL_PRICE, $minimalPrice);

        return $this;
    }

    /**
     * Is user remove item from cart manually.
     * Disable AutoAdd functionality.
     *
     * @param bool|null $flag
     *
     * @return bool
     */
    public function isItemDeleted($flag = null)
    {
        if ($flag !== null) {
            $this->setData(self::IS_ITEM_DELETED, $flag);
        }

        return (bool)$this->_getData(self::IS_ITEM_DELETED);
    }

    /**
     * @return string
     */
    public function getDiscountItem()
    {
        return $this->_getData(self::DISCOUNT_ITEM);
    }

    /**
     * @param string $discountItem
     *
     * @return $this
     */
    public function setDiscountItem($discountItem)
    {
        $this->setData(self::DISCOUNT_ITEM, $discountItem);

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * @param float $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->setData(self::DISCOUNT_AMOUNT, $discountAmount);

        return $this;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->_getData(self::RULE_ID);
    }

    /**
     * @param int $ruleId
     *
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        $this->setData(self::RULE_ID, $ruleId);

        return $this;
    }

    /**
     * @return int
     */
    public function getRuleType()
    {
        return $this->_getData(self::RULE_TYPE);
    }

    /**
     * @param int $ruleType
     *
     * @return $this
     */
    public function setRuleType($ruleType)
    {
        $this->setData(self::RULE_TYPE, $ruleType);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoAdd()
    {
        return (bool)$this->_getData(self::AUTO_ADD);
    }

    /**
     * @param bool $autoAdd
     *
     * @return $this
     */
    public function setAutoAdd($autoAdd)
    {
        $this->setData(self::AUTO_ADD, $autoAdd);

        return $this;
    }

    public function getQuoteId(): int
    {
        return (int)$this->_getData(self::QUOTE_ID);
    }

    public function setQuoteId(int $quoteId): void
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }
}
