<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid\Promo\Modifiers;

use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\PromoItemRepository;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ModifyPromoPrice implements ModifierInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var Quote
     */
    private $backendQuoteSession;

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        DiscountCalculator $discountCalculator,
        Quote $backendQuoteSession,
        PromoItemRepository $promoItemRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->discountCalculator = $discountCalculator;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$this->backendQuoteSession->getQuoteId());

        foreach ($data['items'] as &$item) {
            $promo = current($promoItemsGroup->getItemsBySku($item['sku']));
            if (!empty($item['min_price'])) {
                $baseDiscount = 0;
                if ($promo->getDiscountItem()) {
                    $baseDiscount = $this->discountCalculator->getBaseDiscount(
                        (string)$promo->getDiscountItem(),
                        (float)$item['min_price']
                    );
                }
                $item['min_price'] = $this->priceCurrency->convert($item['min_price']);

                $promoPrice = $this->priceCurrency->convert(
                    ($baseDiscount) ? ($item['min_price'] - $baseDiscount) : 0
                );

                $item['promo_price'] = ($promoPrice < 0) ? 0 : $promoPrice;
            }
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
