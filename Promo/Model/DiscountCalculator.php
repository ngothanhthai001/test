<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model;

use Magento\Quote\Model\Quote\Item;

/**
 * Calculator for promo items (free gift) price discount
 */
class DiscountCalculator
{
    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    public function __construct(
        \Magento\Store\Model\Store $store,
        \Amasty\Promo\Model\Config $config,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->store = $store;
        $this->config = $config;
        $this->taxConfig = $taxConfig;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Item $item
     *
     * @return float|int|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        /** @var Rule $promoRule */
        $promoRule = $rule->getAmpromoRule();
        $promoDiscount = trim($promoRule->getItemsDiscount() ?? 0);

        /** Apply Discount On Prices Including Tax */
        if ($item->getTaxAmount() && $this->taxConfig->discountTax()) {
            $itemPrice = $item->getBasePriceInclTax();
        } else {
            $itemPrice = $item->getBasePrice();
        }

        // Take into account discount from other rules with higher priority.
        // It's important when use 'Promo Items Discount' setting.
        $itemPrice -= $item->getBaseDiscountAmount();

        $baseDiscount = $this->getBaseDiscount($promoDiscount, $itemPrice);

        return $this->getDiscountAfterMinimalPrice($promoRule, $itemPrice, $baseDiscount) * $item->getQty();
    }

    /**
     * @param string $promoDiscount
     * @param float $itemPrice
     * @return float|int
     */
    public function getBaseDiscount(string $promoDiscount, float $itemPrice)
    {
        switch (true) {
            case $promoDiscount === "100%":
            case $promoDiscount == "":
                $baseDiscount = $itemPrice;
                break;

            case strpos($promoDiscount, "%") !== false:
                $baseDiscount = $this->getPercentDiscount($itemPrice, $promoDiscount);
                break;

            case strpos($promoDiscount, "-") !== false:
                $baseDiscount = $this->getFixedDiscount($itemPrice, $promoDiscount);
                break;

            default:
                $baseDiscount = $this->getFixedPrice($itemPrice, $promoDiscount);
                break;
        }

        return $baseDiscount;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        $discountAmount = $this->getBaseDiscountAmount($rule, $item) * $this->store->getCurrentCurrencyRate();

        return $discountAmount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getPercentDiscount($itemPrice, $promoDiscount)
    {
        $percent = (float)str_replace("%", "", $promoDiscount);
        $discount = $itemPrice * $percent / 100;

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedDiscount($itemPrice, $promoDiscount)
    {
        $discount = abs($promoDiscount);
        if ($discount > $itemPrice) {
            $discount = $itemPrice;
        }

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedPrice($itemPrice, $promoDiscount)
    {
        $discount = $itemPrice - (float)$promoDiscount;
        if ($discount < 0) {
            $discount = 0;
        }

        return $discount;
    }

    /**
     * @param Rule $promoRule
     * @param float $itemPrice
     * @param float $discount
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDiscountAfterMinimalPrice(Rule $promoRule, $itemPrice, $discount)
    {
        $minimalPrice = (float)$promoRule->getMinimalItemsPrice();

        if ($itemPrice > $minimalPrice && $itemPrice - $discount < $minimalPrice) {
            $discount = $itemPrice - $minimalPrice;
        }

        return $discount;
    }

    /**
     * @param array $discount
     *
     * @return bool
     */
    public function isFullDiscount($discount)
    {
        if ($discount) {
            $discountItem = $discount['discount_item'] ?? '';
            $minimalPrice = $discount['minimal_price'] ?? '';
            if ($minimalPrice) {
                return false;
            }

            return empty($discountItem) || $discountItem === "100%";
        }

        return false;
    }

    /**
     * @param $discount
     *
     * @return bool
     */
    public function isEnableAutoAdd($discount)
    {
        $addAutomatically = $this->config->getAutoAddType();

        return ($addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_ITEMS
                && $this->isFullDiscount($discount))
            || $addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_DISCOUNTED_ITEMS;
    }
}
