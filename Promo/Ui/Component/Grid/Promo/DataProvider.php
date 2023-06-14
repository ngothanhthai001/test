<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid\Promo;

use Amasty\Promo\Helper\Data;
use Amasty\Promo\Model\Product;
use Amasty\Promo\Model\PromoItemRepository;
use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    private const MIN_REQUIRED_QTY = 1;
    private const MIN_PRICE_FIELD = 'min_price';

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    /**
     * @var Quote
     */
    private $backendQuote;

    /**
     * @var Product
     */
    private $productStockProvider;

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Quote $backendQuote,
        Product $productStockProvider,
        PromoItemRepository $promoItemRepository,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data,
            $modifiersPool
        );
        $this->modifiersPool = $modifiersPool;
        $this->backendQuote = $backendQuote;
        $this->productStockProvider = $productStockProvider;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        $allowedSkus = $this->getAllowedSkus();
        
        $this->collection
            ->joinField(
                'stock_status',
                'cataloginventory_stock_status',
                'stock_status',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )
            ->addFieldToFilter('sku', ['in' => $allowedSkus])
            ->addFieldToFilter('type_id', ['in' => Data::ALLOWED_PRODUCT_TYPES])
            ->addFieldToFilter(
                'stock_status',
                ['eq' => StockStatus::STATUS_IN_STOCK]
            );

        return parent::getData();
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        foreach ($this->modifiersPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
    
    private function getAllowedSkus(): array
    {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$this->backendQuote->getQuoteId());
        $allowedSkus = $promoItemsGroup->getAllowedSkus();

        foreach ($allowedSkus as $key => $promoItem) {
            $availableQty = $this->productStockProvider->checkAvailableQty(
                $promoItem,
                self::MIN_REQUIRED_QTY,
                $this->backendQuote->getQuote()
            );
            if (!$availableQty) {
                unset($allowedSkus[$key]);
            }
        }
        
        return $allowedSkus;
    }

    /**
     * @param string|array $field
     * @param string|null $alias
     */
    public function addField($field, $alias = null): void
    {
        if ($field === self::MIN_PRICE_FIELD) {
            $this->getCollection()->addFinalPrice();
        }
        parent::addField($field, $alias);
    }

    /**
     * @param string $field
     * @param string $direction
     */
    public function addOrder($field, $direction): void
    {
        if ($field === self::MIN_PRICE_FIELD) {
            $this->getCollection()->getSelect()->order($field . ' ' . $direction);
        }
        parent::addOrder($field, $direction);
    }
}
