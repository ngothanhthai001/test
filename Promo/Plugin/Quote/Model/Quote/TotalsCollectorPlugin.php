<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Quote\Model\Quote;

use Amasty\Promo\Helper\Cart;
use Amasty\Promo\Helper\Item as ItemHelper;
use Amasty\Promo\Model\Config;
use Amasty\Promo\Model\PromoItemRepository;
use Amasty\Promo\Model\Registry;
use Amasty\Promo\Model\Storage;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Checking qty of Promo items and automatically add Promo Items to cart if necessary
 *
 * @since 2.5.4
 */
class TotalsCollectorPlugin
{
    public const KEY_IS_ADDRESS_PROCESSED = 'amastyFreeGiftProcessed';

    /**
     * @var Cart
     */
    private $promoCartHelper;

    /**
     * @var ItemHelper
     */
    private $promoItemHelper;

    /**
     * @var Registry
     */
    private $promoRegistry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * flag for recollect totals
     *
     * @var bool
     */
    protected $recollectTotals = false;

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        Cart $promoCartHelper,
        ItemHelper $promoItemHelper,
        Registry $promoRegistry,
        Config $config,
        ManagerInterface $eventManager,
        ProductRepository $productRepository,
        Storage $storage,
        PromoItemRepository $promoItemRepository
    ) {
        $this->promoCartHelper = $promoCartHelper;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        $this->storage = $storage;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * ReCalculate Totals if items was updated dynamically
     *
     * @param TotalsCollector $subject
     * @param callable $proceed
     * @param Quote $quote
     * @param Address $address
     *
     * @return Total
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollectAddressTotals(
        TotalsCollector $subject,
        callable $proceed,
        Quote $quote,
        Address $address
    ) {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quote->getId());

        // Reset isDeleted flag when delete all products from the cart
        // for saving the opportunity of automatic adding promo products to the cart
        if (!$quote->getAllItems() && !$address->getData(self::KEY_IS_ADDRESS_PROCESSED)) {
            $promoItemsGroup->resetDeletedItems();
        }

        if (!$address->getAllItems() || $address->getData(self::KEY_IS_ADDRESS_PROCESSED)) {
            return $proceed($quote, $address);
        }

        $this->recollectTotals = false;
        if ($address->getAllItems()) {
            $promoItemsGroup->resetQtyAllowed();
        }

        $totals = $proceed($quote, $address);

        $address->setData(self::KEY_IS_ADDRESS_PROCESSED, true);
        $this->updateQuoteItems($quote);
        if ($this->storage->isAutoAddAllowed()) {
            $this->addProductsAutomatically($quote);
        } elseif (!$this->recollectTotals && $promoItemsGroup->getItemsForAutoAdd()) {
            //save estimation address
            $this->storage->setIsQuoteSaveRequired(true);
        }

        if ($this->recollectTotals) {
            $this->promoCartHelper->updateTotalQty($quote);
            $address->unsetData('cached_items_all');
            $address->setCollectShippingRates(true);
            $quote->setCartFixedRules([]);

            //execute closure one more time for recalculate totals
            $totals = $proceed($quote, $address);
            $this->storage->setIsQuoteSaveRequired(true);
        }

        return $totals;
    }

    /**
     * If applicable, add products to cart automatically
     *
     * @param Quote $quote
     *
     * @since 2.8.0 product get without force load
     */
    public function addProductsAutomatically($quote)
    {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quote->getId());
        foreach ($promoItemsGroup->getItemsForAutoAdd() as $promoItem) {
            $product = $this->getProductForAutoAdd($promoItem->getSku());

            $isAdded = $this->promoCartHelper->addProduct(
                $product,
                $promoItem->getQtyToProcess(),
                $promoItem,
                [],
                $quote
            );

            if (!$this->recollectTotals && $isAdded) {
                $this->recollectTotals = true;
            }
        }
    }

    /**
     * load product from cache and remove all quote item data
     * to prevent connection with same product
     *
     * @param string $sku
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     *
     * @since 2.8.0
     */
    public function getProductForAutoAdd($sku)
    {
        $product = clone $this->productRepository->get($sku);
        $product->unsetData('ampromo_rule_id');
        $product->unsetData('parent_product_id');

        return $product;
    }

    /**
     * Update Quote Items quantity added by Free Gift
     *
     * @param Quote $quote
     */
    public function updateQuoteItems($quote)
    {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quote->getId());
        $promoItemsGroup->resetQtyReserve();
        /** @var Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getParentItem() && $this->promoItemHelper->isPromoItem($item)) {
                $sku = $item->getProduct()->getData('sku');

                $ruleId = $this->promoItemHelper->getRuleId($item);
                $item->setQuote($quote);
                $promoData = $promoItemsGroup->getItemBySkuAndRuleId($sku, $ruleId);
                if (!$promoData || (float)$promoData->getQtyToProcess() <= 0.00001) {
                    $this->removeGift($item);
                    $this->recollectTotals = true;
                    continue;
                }
                if ((float)$item->getQty() > (float)$promoData->getQtyToProcess()) {
                    $item->setQty($promoData->getQtyToProcess());
                    $this->recollectTotals = true;
                }
                $promoItemsGroup->assignQtyToItem(
                    $item->getQty(),
                    $promoData
                );
            }
        }
    }

    /**
     * @param Item $item
     */
    private function removeGift($item)
    {
        $quote = $item->getQuote();
        if ($item->getId()) {
            $quote->removeItem($item->getId());
        } else {
            $item->isItemDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isItemDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isItemDeleted(true);
            }
            $this->eventManager->dispatch('sales_quote_remove_item', ['quote_item' => $item]);

            //reassemble collection items, otherwise 'deleted' items without ID will be saved
            $collection = $quote->getItemsCollection();
            $items = $collection->getItems();
            $collection->removeAllItems();

            /** @var Item $row */
            foreach ($items as $row) {
                if ($row->getId() || !$row->isDeleted()) {
                    $collection->addItem($row);
                }
            }
        }
    }
}
