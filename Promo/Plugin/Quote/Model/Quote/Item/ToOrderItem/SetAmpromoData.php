<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Quote\Model\Quote\Item\ToOrderItem;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;

class SetAmpromoData
{
    /**
     * @see ToOrderItem::convert()
     *
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $data
     *
     * @return OrderItemInterface
     */
    public function afterConvert(
        ToOrderItem $subject,
        OrderItemInterface $orderItem,
        AbstractItem $item,
        $data = []
    ) {
        $productItemOptions = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());

        if ($productItemOptions && isset($productItemOptions['info_buyRequest']['options']['ampromo_rule_id'])) {
            $productOptions = $orderItem->getProductOptions();

            if (empty($productOptions['info_buyRequest']['options'])) {
                $productOptions['info_buyRequest']['options'] = $productItemOptions['info_buyRequest']['options'];
                $orderItem->setProductOptions($productOptions);
            }
        }

        return $orderItem;
    }
}
