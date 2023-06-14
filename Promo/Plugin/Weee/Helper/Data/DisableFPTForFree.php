<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Weee\Helper\Data;

use Amasty\Promo\Helper\Item;
use Amasty\Promo\Model\ResourceModel\Rule;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Website;
use Magento\Weee\Helper\Data;

class DisableFPTForFree
{
    /**
     * @var Item
     */
    private $promoItemHelper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Rule
     */
    private $rule;

    public function __construct(
        Item $promoItemHelper,
        SerializerInterface $serializer,
        Rule $rule
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->serializer = $serializer;
        $this->rule = $rule;
    }

    /**
     * @param Data $subject
     * @param DataObject[] $result
     * @param Product $product
     * @param false|\Magento\Framework\DataObject|null $shipping
     * @param false|\Magento\Framework\DataObject|null $billing
     * @param Website|null $website
     * @param bool $calculateTaxes
     * @param bool $round
     * @return DataObject[]
     */
    public function afterGetProductWeeeAttributes(
        Data $subject,
        array $result,
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTaxes = false,
        $round = true
    ): array {
        $ruleId = $this->getAmpromoRuleId($product);
        if ($ruleId && !$this->rule->isApplyTax($ruleId)) {
            return [];
        }

        return $result;
    }

    private function getAmpromoRuleId(Product $product): ?int
    {
        $buyRequest = $product->getCustomOption('info_buyRequest');
        $buyRequest = $buyRequest ? $this->serializer->unserialize($buyRequest->getValue()) : [];

        return $this->promoItemHelper->getRuleIdFromBuyRequest($buyRequest);
    }
}
