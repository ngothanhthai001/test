<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Observer;

use Amasty\Promo\Model\PromoItemRepository;
use Magento\Framework\Event\ObserverInterface;

/**
 * Mart promo data when user update qty of promo item manually
 */
class CheckoutCartUpdateItemsBefore implements ObserverInterface
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helperItem;

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        \Amasty\Promo\Helper\Item $helperItem,
        PromoItemRepository $promoItemRepository
    ) {
        $this->helperItem = $helperItem;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getInfo()->toArray();
        /** @var \Magento\Checkout\Model\Cart\CartInterface $cart */
        $cart = $observer->getCart();
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$cart->getQuote()->getId());
        foreach ($data as $itemId => &$itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);

            if ($item && $this->helperItem->isPromoItem($item) && $itemInfo['qty'] != $item->getQty()) {
                $promoItemData = $promoItemsGroup->getItemBySkuAndRuleId(
                    $item->getProduct()->getData('sku'),
                    $this->helperItem->getRuleId($item)
                );
                if ($promoItemData && $promoItemData->isAutoAdd()) {
                    //disable auto add functionality if customer changing qty manually
                    $promoItemData->isItemDeleted(true);
                }
            }
        }
    }
}
