<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model;

use Amasty\Promo\Api\PromoItemRepositoryInterface;
use Amasty\Promo\Model\ItemRegistry\PromoItemsGroup;
use Amasty\Promo\Model\ItemRegistry\PromoItemsGroupFactory;
use Amasty\Promo\Model\ResourceModel\PromoItem;
use Amasty\Promo\Model\ResourceModel\PromoItem\CollectionFactory;

class PromoItemRepository implements PromoItemRepositoryInterface
{
    /**
     * @var array
     */
    private $itemsStorage = [];

    /**
     * @var ResourceModel\PromoItem
     */
    private $promoItemResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var mixed
     */
    private $promoItemsGroupFactory;

    public function __construct(
        PromoItem $promoItemResource,
        CollectionFactory $collectionFactory,
        PromoItemsGroupFactory $promoItemsGroupFactory
    ) {
        $this->promoItemResource = $promoItemResource;
        $this->collectionFactory = $collectionFactory;
        $this->promoItemsGroupFactory = $promoItemsGroupFactory;
    }

    /**
     * @param int $quoteId
     * @return PromoItemsGroup
     */
    public function getItemsByQuoteId(int $quoteId): PromoItemsGroup
    {
        // set items for new quote when save quote first time
        if ($quoteId !== 0
            && isset($this->itemsStorage[0])
            && !isset($this->itemsStorage[$quoteId])
        ) {
            $this->itemsStorage[$quoteId] = $this->itemsStorage[0];
            unset($this->itemsStorage[0]);
        }
        if (!isset($this->itemsStorage[$quoteId])) {
            /** @var \Amasty\Promo\Model\ResourceModel\PromoItem\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('quote_id', $quoteId);
            $promoItemsGroup = $this->promoItemsGroupFactory->create();
            $promoItemsGroup->setItems($collection->getItems());
            $this->itemsStorage[$quoteId] = $promoItemsGroup;
        }

        return $this->itemsStorage[$quoteId];
    }

    /**
     * @param int $quoteId
     * @return void
     */
    public function saveItems(int $quoteId): void
    {
        $promoItemsGroup = $this->getItemsByQuoteId($quoteId);
        $this->promoItemResource->deleteByQuoteId($quoteId);
        foreach ($promoItemsGroup->getItemsForSave() as $promoItem) {
            $promoItem->setQuoteId($quoteId);
            $promoItem->setDataChanges(true);
            $this->promoItemResource->save($promoItem);
        }
    }
}
