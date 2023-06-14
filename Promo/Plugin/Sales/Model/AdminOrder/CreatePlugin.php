<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Sales\Model\AdminOrder;

use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ItemRepository;

class CreatePlugin
{
    /**
     * @var ItemRepository
     */
    private $orderItemRepository;

    public function __construct(ItemRepository $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    public function afterCreateOrder(Create $subject, Order $order)
    {
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getItems() as $orderItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            foreach ($subject->getQuote()->getAllItems() as $quoteItem) {
                if ($orderItem->getQuoteItemId() === $quoteItem->getItemId()) {
                    $productOptions = $orderItem->getProductOptions();
                    $quoteItemBuyRequestOptions = $quoteItem->getBuyRequest()->getData();
                    if (!empty($quoteItemBuyRequestOptions['options'])) {
                        $productOptions['info_buyRequest']['options'] = $quoteItemBuyRequestOptions['options'];
                        $orderItem->setProductOptions($productOptions);
                        $this->orderItemRepository->save($orderItem);
                    }
                }
            }
        }
        return $order;
    }
}
