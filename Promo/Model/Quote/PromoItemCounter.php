<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\Quote;

use Amasty\Promo\Api\Data\CounterInterface;
use Amasty\Promo\Api\Data\CounterInterfaceFactory;
use Amasty\Promo\Helper\Data;
use Amasty\Promo\Model\Product;
use Magento\Quote\Model\Quote;

class PromoItemCounter
{
    private const MIN_REQUIRED_QTY = 1;

    /**
     * @var Data
     */
    private $promoHelper;

    /**
     * @var CounterInterfaceFactory
     */
    private $counterInterfaceFactory;

    /**
     * @var Product
     */
    private $productStockProvider;

    public function __construct(
        Data $promoHelper,
        Product $productStockProvider,
        CounterInterfaceFactory $counterInterfaceFactory
    ) {
        $this->promoHelper = $promoHelper;
        $this->productStockProvider = $productStockProvider;
        $this->counterInterfaceFactory = $counterInterfaceFactory;
    }

    /**
     * @param Quote $quote
     * @return CounterInterface
     */
    public function getPromoCounts(Quote $quote): CounterInterface
    {
        $availableCount = 0;
        $promoSelectedCount = 0;
        $visibleItems = $quote->getAllVisibleItems();
        $promoItems = $this->promoHelper->getNewItems((int)$quote->getId());
        $promoItemsData = $this->promoHelper->getPromoItemsDataArray($quote);

        foreach ($promoItems as $promoItem) {
            $promoItemSku = $promoItem->getSku();
            if (isset($promoItemsData['promo_sku'][$promoItemSku])) {
                $availableCount += $this->productStockProvider->checkAvailableQty(
                    $promoItemSku,
                    $promoItemsData['promo_sku'][$promoItemSku]['qty'],
                    $quote
                );
            }
        }

        if ($visibleItems) {
            foreach ($visibleItems as $item) {
                if ($item->getAmpromoRuleId()) {
                    $promoSelectedCount += $item->getQty();
                }
            }
        }

        $availableCount = min($promoItemsData['common_qty'], $availableCount);

        return $this->counterInterfaceFactory->create(
            [
                'data' => [
                    CounterInterface::KEY_AVAILABLE => $availableCount,
                    CounterInterface::KEY_SELECTED => $promoSelectedCount
                ]
            ]
        );
    }
}
