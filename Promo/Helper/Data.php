<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Helper;

use Amasty\Promo\Model\PromoItemRepository;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\Framework\DB\Sql\Expression;
use Magento\Quote\Model\Quote;

/**
 * Helper probably will be moved/separated
 */
class Data
{
    /**
     * Allowed product types for precess as Free Gift (Promo Item)
     */
    public const ALLOWED_PRODUCT_TYPES = [
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
        \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
        \Magento\Bundle\Model\Product\Type::TYPE_CODE,
        'giftcard',//EE
    ];

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    private $promoMessagesHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|null|false
     */
    protected $productsCache = null;

    /**
     * @var array|null
     */
    protected $itemsPopupDataCache = null;

    /**
     * @var \Amasty\Promo\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Amasty\Promo\Model\Product $product,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        PromoItemRepository $promoItemRepository
    ) {
        $this->promoRegistry = $promoRegistry;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->product = $product;
        $this->collectionFactory = $collectionFactory;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * Reset local cache
     */
    public function resetStorage()
    {
        $this->productsCache = $this->itemsPopupDataCache = null;
    }

    /**
     * @return array
     */
    public function getNewItems(int $quoteId)
    {
        if ($this->productsCache === null) {
            $this->productsCache = [];
            $this->promoRegistry->updatePromoItemsReservedQty();
            $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId($quoteId);
            if (!$allowedSku = $promoItemsGroup->getAllowedSkus()) {
                return [];
            }
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
            $products = $this->collectionFactory->create()
                ->joinField(
                    'stock_status',
                    'cataloginventory_stock_status',
                    'stock_status',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )
                ->addAttributeToSelect(['name', 'small_image', 'status', 'visibility'])
                ->addFieldToFilter('sku', ['in' => $allowedSku])
                ->addFieldToFilter(
                    'stock_status',
                    ['eq' => StockStatus::STATUS_IN_STOCK]
                );

            // Sort items by promo rule SKUs in the order, same as saved by admin.
            $products->getSelect()
                ->order(new Expression("FIELD(e.sku," . $products->getConnection()->quote($allowedSku) . ")"));

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), static::ALLOWED_PRODUCT_TYPES)) {
                    $this->promoMessagesHelper->showMessage(__(
                        "We apologize, but products of type <strong>%1</strong> are not supported",
                        $product->getTypeId()
                    ));

                    $products->removeItemByKey($key);
                }

                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                    && (!$product->isSalable() || !$this->product->checkAvailableQty($product->getSku(), 1))
                ) {
                    $this->promoMessagesHelper->addAvailabilityError($product);

                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }
            }

            if ($products->getItems()) {
                $this->productsCache = $products->getItems();
            }
        }

        return $this->productsCache;
    }

    /**
     * Gat data for popup
     *
     * @return array
     */
    public function getPromoItemsDataArray(Quote $quote)
    {
        if ($this->itemsPopupDataCache !== null) {
            return $this->itemsPopupDataCache;
        }

        $promoSkus = $discountData = [];
        $this->promoRegistry->updatePromoItemsReservedQty($quote);
        $qtyByRule = [0 => 0];
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quote->getId());

        foreach ($promoItemsGroup->getAllowedItems() as $promoItemData) {
            $sku = $promoItemData->getSku();
            $ruleId = $promoItemData->getRuleId();
            $itemQty = $promoItemData->getQtyToProcess();
            if ($promoItemData->getRuleType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ONE) {
                // items with rule type 'one of' have qty for group
                // must be before qty fix
                $qtyByRule[$promoItemData->getRuleId()] = $itemQty;
            }

            $itemQty = $this->fixQty($sku, $itemQty);

            if ($promoItemData->getRuleType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ALL) {
                // items with rule type 'all' have qty for each item
                // should be after qty fix
                $qtyByRule[0] += $itemQty;
            }

            $itemData = [
                'discount' => $promoItemData->getDiscountArray(),
                'qty' => $itemQty,
                'available_qty' => $this->product->checkAvailableQty($sku, $itemQty)
            ];
            $discountData[$ruleId]['rule_type'] = $promoItemData->getRuleType();
            $discountData[$ruleId]['discount_amount'] = $promoItemData->getDiscountAmount();
            $discountData[$ruleId]['sku'][$sku] = $itemData;
            $promoSkus[$sku] = &$discountData[$ruleId]['sku'][$sku];
        }

        return $this->itemsPopupDataCache = [
            'common_qty' => array_sum($qtyByRule),
            'triggered_products' => $discountData,
            'promo_sku' => $promoSkus
        ];
    }

    /**
     * Check available inventory
     *
     * @param string $sku
     * @param int|float $qty
     *
     * @return float|int
     */
    protected function fixQty($sku, $qty)
    {
        return $this->product->checkAvailableQty($sku, $qty);
    }
}
