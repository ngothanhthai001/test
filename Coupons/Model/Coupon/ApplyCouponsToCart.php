<?php

declare(strict_types=1);

namespace Amasty\Coupons\Model\Coupon;

use Amasty\Coupons\Api\Data\CouponApplyResultListInterface;
use Amasty\Coupons\Api\Data\CouponApplyResultListInterfaceFactory;
use Amasty\Coupons\Model\Config;
use Amasty\Coupons\Model\Quote\CartItemsSnapshotManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Escaper;

class ApplyCouponsToCart implements \Amasty\Coupons\Api\ApplyCouponsToCartInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Amasty\Coupons\Api\Data\CouponApplyResultInterfaceFactory
     */
    private $couponResultFactory;

    /**
     * @var \Magento\Quote\Api\CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var \Amasty\Coupons\Model\CouponRenderer
     */
    private $couponRenderer;

    /**
     * @var CartItemsSnapshotManager
     */
    private $cartItemsSnapshotManager;

    /**
     * @var CouponApplyResultListInterfaceFactory
     */
    private $couponApplyResultListFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Amasty\Coupons\Api\Data\CouponApplyResultInterfaceFactory $couponResultFactory,
        \Magento\Quote\Api\CouponManagementInterface $couponManagement,
        \Amasty\Coupons\Model\CouponRenderer $couponRenderer,
        CartItemsSnapshotManager $cartItemsSnapshotManager,
        CouponApplyResultListInterfaceFactory $couponApplyResultListFactory,
        Config $config,
        Escaper $escaper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->couponResultFactory = $couponResultFactory;
        $this->couponManagement = $couponManagement;
        $this->couponRenderer = $couponRenderer;
        $this->cartItemsSnapshotManager = $cartItemsSnapshotManager;
        $this->couponApplyResultListFactory = $couponApplyResultListFactory;
        $this->config = $config;
        $this->escaper = $escaper;
        $this->objectManager = $objectManager;
}

    /**
     * Try to apply list of coupons.
     * Return lists of applied and failed coupons.
     *
     * @param int $cartId The cart ID.
     * @param string[] $couponCodes The coupon code data.
     *
     * @return \Amasty\Coupons\Api\Data\CouponApplyResultInterface[]
     */
    public function apply(int $cartId, array $couponCodes): array
    {
        return $this->applyToCart($cartId, $couponCodes)->getItems();
    }

    /**
     * @param array $couponCodes
     *
     * @return array
     */
    private function filterCoupons(array $couponCodes): array
    {
        $inputCoupons = [];

        foreach ($couponCodes as $code) {
            if ($this->couponRenderer->findCouponInArray($code, $inputCoupons) === false) {
                $inputCoupons[] = $code;
            }
        }

        return $inputCoupons;
    }

    /**
     * @param int $cartId
     * @param array $couponCodes
     * @return CouponApplyResultListInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function applyToCart(int $cartId, array $couponCodes): CouponApplyResultListInterface
    {
        $couponCodes = $this->filterCoupons($couponCodes);

        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItemsSnapshot = $this->cartItemsSnapshotManager->takeSnapshot($quote);
        try {
            $this->couponManagement->set($cartId, implode(',', $couponCodes));

            $couponDisable = $this->config->getCouponDisable();
            $couponDisableArray = explode(",", $couponDisable ?: "");

            $couponDisableId = $this->config->getCouponDisableId();

            $couponIdDisableArray = explode(",", $couponDisableId ?: "");

            $appliedRuleId = $quote->getAppliedRuleIds();

            $appliedRuleIdArray = explode(",", $appliedRuleId ?: "");

            if(!array_intersect($couponCodes , $couponDisableArray)
                && !array_intersect($appliedRuleIdArray , $couponIdDisableArray)
            ) {
                $maxDiscount = $this->config->getLimitDiscount($quote);

                $subtotal = $quote->getSubtotal();

                $subtotalWithDiscount = $quote->getSubtotalWithDiscount();

                $discount = $subtotal - $subtotalWithDiscount;
                if ($maxDiscount != null) {
                    if ($maxDiscount < $discount) {
                        $couponCodeArray = explode(",", $quote->getCouponCode());
                        $remove = array_pop($couponCodeArray);
                        $couponCode = implode(',', $couponCodeArray);
                        $quote->setCouponCode($couponCode)->collectTotals();
                        $this->quoteRepository->save($quote);
                    }
                }

                if(isset($remove)) {
                    throw new CouldNotSaveException(
                        __('The coupon code "%1" excess limit.', $remove)
                    );
                }
            }

        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            if (!$quote->getItemsCount() || !$quote->getStoreId()) {
                throw $exception;
            }
        }
        $appliedCodes = $this->couponRenderer->render($quote->getCouponCode());

        $couponResultItems = [];
        foreach ($couponCodes as $code) {
            $couponKey = $this->couponRenderer->findCouponInArray($code, $appliedCodes);
            $isApplied = false;
            if ($couponKey !== false) {
                $code = $appliedCodes[$couponKey];
                $isApplied = true;
            }

            $couponResultItems[] = $this->couponResultFactory->create(
                ['isApplied' => $isApplied, 'code' => $code]
            );
        }

        $result = $this->couponApplyResultListFactory->create();
        $result->setItems($couponResultItems);
        $result->setIsQuoteItemsChanged(
            !$this->cartItemsSnapshotManager->isEqualWithSnapshot($quote, $quoteItemsSnapshot)
        );

        return $result;
    }
}
