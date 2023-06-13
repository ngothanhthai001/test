<?php

declare(strict_types=1);

namespace Amasty\Coupons\Plugin\Cart;

use Amasty\Coupons\Api\Data\DiscountBreakdownLineInterface;
use Amasty\Coupons\Api\Data\DiscountBreakdownLineInterfaceFactory;
use Amasty\Coupons\Model\DiscountCollector;
use Amasty\Coupons\Model\SalesRule\CouponListProvider;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\CouponFactory;

/**
 * Insert coupons discount breakdown data.
 */
class CartTotalRepositoryPlugin
{
    /**
     * @var TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var DiscountCollector
     */
    private $discountRegistry;

    /**
     * @var DiscountBreakdownLineInterfaceFactory
     */
    private $discountBreakdownFactory;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    /**
     * @param DiscountCollector $discountRegistry
     * @param TotalsExtensionFactory $totalsExtensionFactory
     * @param DiscountBreakdownLineInterfaceFactory $discountBreakdownFactory
     * @param CouponListProvider $couponListProvider
     * @param CouponFactory $couponFactory
     */
    public function __construct(
        DiscountCollector $discountRegistry,
        TotalsExtensionFactory $totalsExtensionFactory,
        DiscountBreakdownLineInterfaceFactory $discountBreakdownFactory,
        CouponListProvider $couponListProvider,
        CouponFactory $couponFactory
    ) {
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->discountRegistry = $discountRegistry;
        $this->discountBreakdownFactory = $discountBreakdownFactory;
        $this->couponListProvider = $couponListProvider;
        $this->couponFactory = $couponFactory;
    }

    /**
     * Set extension attributes.
     *
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface $quoteTotals
     *
     * @return TotalsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        TotalsInterface $quoteTotals
    ) {
        $couponCodes = $this->discountRegistry->getCouponCodes();
        if (empty($couponCodes)) {
            return $quoteTotals;
        }

        $couponModels = $this->couponListProvider->getItemsByCodes($couponCodes);

        $extensionAttributes = $quoteTotals->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->totalsExtensionFactory->create();
        }

        $discounts = [];

        foreach ($this->discountRegistry->getRulesWithAmount() as $couponData) {
            $couponModel = $couponModels[$couponData['coupon_code']] ?? null;

            if (!$couponModel) {
                continue;
            }

            $discounts[] = $this->discountBreakdownFactory->create(
                [
                    'data' => [
                        DiscountBreakdownLineInterface::RULE_ID => (int)$couponModel->getRuleId(),
                        DiscountBreakdownLineInterface::RULE_NAME => $couponData['coupon_code'],
                        DiscountBreakdownLineInterface::RULE_AMOUNT => $couponData['coupon_amount'],
                        DiscountBreakdownLineInterface::RULE_LABEL => $couponData['coupon_label']
                    ]
                ]
            );
        }

        $extensionAttributes->setAmcouponDiscountBreakdown($discounts);
        $quoteTotals->setExtensionAttributes($extensionAttributes);

        return $quoteTotals;
    }
}
