<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Test\Unit\Plugin\Quote\Model\Quote;

use Amasty\Promo\Model\ItemRegistry\PromoItemsGroup;
use Amasty\Promo\Model\PromoItemRepository;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class TotalsCollectorPlugin extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const PROMO_ITEM_SKU = 'test_sku';

    public const RULE_ID = 1;

    public const QTY_TO_PROCESS = 5;

    /**
     * @var \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin|MockObject
     */
    private $collectTotalsObserver;

    /**
     * @var PromoItemRepository|MockObject
     */
    private $promoItemRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|MockObject
     */
    private $productRepository;

    /**
     * @var \Amasty\Promo\Helper\Cart|MockObject
     */
    private $promoCartHelper;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    private $product;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject
     */
    private $promoItem;

    /**
     * @var \Amasty\Promo\Helper\Item|MockObject
     */
    private $promoItemHelper;

    /**
     * @var PromoItemsGroup
     */
    private $promoItemGroup;

    public function setUp(): void
    {
        $this->collectTotalsObserver = $this->createPartialMock(\Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::class, []);

        $this->promoItem = $this->createPartialMock(
            \Amasty\Promo\Model\ItemRegistry\PromoItemData::class,
            []
        );
        $this->promoItem->setSku(self::PROMO_ITEM_SKU)->setAllowedQty(6)->setReservedQty(1);
        $this->promoItemGroup = $this->createPartialMock(
            PromoItemsGroup::class,
            ['getItemsForAutoAdd', 'getItemBySkuAndRuleId']
        );
        $this->promoItemGroup->expects($this->any())->method('getItemsForAutoAdd')
            ->willReturn([$this->promoItem]);

        $this->promoItemRepository = $this->createConfiguredMock(
            \Amasty\Promo\Model\PromoItemRepository::class,
            ['getItemsByQuoteId' => $this->promoItemGroup]
        );
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productRepository = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->productRepository->expects($this->any())->method('get')
            ->with(self::PROMO_ITEM_SKU)
            ->willReturn($this->product);

        $this->promoCartHelper = $this->createMock(\Amasty\Promo\Helper\Cart::class);
        $this->promoItemHelper = $this->createMock(\Amasty\Promo\Helper\Item::class);

        $this->setProperty(
            $this->collectTotalsObserver,
            'promoItemRepository',
            $this->promoItemRepository,
            \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::class
        );
        $this->setProperty(
            $this->collectTotalsObserver,
            'promoCartHelper',
            $this->promoCartHelper,
            \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::class
        );
        $this->setProperty(
            $this->collectTotalsObserver,
            'productRepository',
            $this->productRepository,
            \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::class
        );
        $this->setProperty(
            $this->collectTotalsObserver,
            'promoItemHelper',
            $this->promoItemHelper,
            \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::class
        );
    }

    /**
     * @covers \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::addProductsAutomatically
     */
    public function testAddProductsAutomatically()
    {
        /** @var \Magento\Quote\Model\Quote|MockObject $quote */
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getAllVisibleItems']
        );
        $this->promoCartHelper->expects($this->once())->method('addProduct')
            ->with($this->product, self::QTY_TO_PROCESS, $this->promoItem);

        $this->collectTotalsObserver->addProductsAutomatically($quote);
    }

    /**
     * @covers \Amasty\Promo\Plugin\Quote\Model\Quote\TotalsCollectorPlugin::updateQuoteItems
     */
    public function testUpdateQuoteItems()
    {
        $quote = $this->initQuote();
        $this->promoItemGroup->expects($this->once())->method('getItemBySkuAndRuleId')
            ->with(self::PROMO_ITEM_SKU, self::RULE_ID)
            ->willReturn($this->promoItem);

        $this->collectTotalsObserver->updateQuoteItems($quote);
    }

    /**
     * Init quote for test
     * @return \Magento\Quote\Model\Quote|MockObject
     */
    private function initQuote()
    {
        /** @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, []);
        $product->setSku(self::PROMO_ITEM_SKU);
        /** @var \Magento\Quote\Model\Quote\Item|MockObject $quoteItem */
        $quoteItem = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getQty', 'setQty']
        );
        $quoteItem->expects($this->once())->method('getProduct')
            ->willReturn($product);
        $quoteItem->expects($this->at(1))->method('getQty')
            ->willReturn(10);
        $quoteItem->expects($this->at(2))->method('getQty')
            ->willReturn(6);
        $this->promoItemHelper->expects($this->once())->method('isPromoItem')
            ->with($quoteItem)
            ->willReturn(true);
        $this->promoItemHelper->expects($this->once())->method('getRuleId')
            ->with($quoteItem)
            ->willReturn(self::RULE_ID);
        /** @var \Magento\Quote\Model\Quote|MockObject $quote */
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getAllVisibleItems']
        );
        $quote->expects($this->once())->method('getAllVisibleItems')
            ->willReturn([$quoteItem]);
        $quote->setId(1)->setStoreId(1);

        return $quote;
    }
}
