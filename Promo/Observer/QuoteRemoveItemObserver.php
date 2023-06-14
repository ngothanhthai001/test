<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Mark item as deleted to prevent it's auto-addition
 *
 * event name: sales_quote_remove_item
 * observer scope: frontend, webapi_rest
 */
class QuoteRemoveItemObserver implements ObserverInterface
{

    public const CHECKOUT_ROUTER = 'amasty_checkout';
    public const CHECKOUT_DELETE = 'remove-item';
    public const GRAPHQL_QUERY = '/graphql';

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Item $item */
        $item = $observer->getEvent()->getQuoteItem();

        // Additional request checks to mark only explicitly deleted items
        if (($this->_request->getActionName() == 'delete'
                && $this->_request->getParam('id') == $item->getId())
            || ($this->_request->getActionName() == 'removeItem'
                && $this->_request->getParam('item_id') == $item->getId())
            || $this->isDeleteFromCheckout()
            || $this->isDeleteFromBackend($item)
            || $this->isDeleteFromGraphQl()
        ) {
            if (!$item->getParentId()
                && $this->promoItemHelper->isPromoItem($item)
            ) {
                $this->promoRegistry->deleteProduct($item);
            }
        }
    }

    /**
     * @return bool
     */
    private function isDeleteFromCheckout()
    {
        $queryString = $this->_request->getRequestString();

        return strpos($queryString, self::CHECKOUT_ROUTER) !== false
            && strpos($queryString, self::CHECKOUT_DELETE) !== false;
    }

    private function isDeleteFromGraphQl(): bool
    {
        $queryString = $this->_request->getRequestString();

        return $queryString === self::GRAPHQL_QUERY;
    }

    private function isDeleteFromBackend(Item $deletedItem): bool
    {
        return $this->_request->getPost('update_items') &&
            $this->isDeleteItem($this->_request->getPost('item'), $deletedItem);
    }

    private function isDeleteItem(array $items, Item $deletedItem): bool
    {
        return !empty($items[$deletedItem->getId()]) && ($items[$deletedItem->getId()]['action'] === 'remove');
    }
}
