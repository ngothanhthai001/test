<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Retrieve specific Cart Item Data
 */
class Item
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param AbstractItem|OrderItem $item
     * @return int|null
     */
    public function getRuleId($item)
    {
        if (!($ruleId = $item->getData('ampromo_rule_id'))) {
            $ruleId = $this->getRuleIdFromBuyRequest($item->getBuyRequest());

            $item->setData('ampromo_rule_id', $ruleId);
        }

        return $ruleId;
    }

    /**
     * @param array|\Magento\Framework\DataObject $buyRequest
     *
     * @return int|null
     */
    public function getRuleIdFromBuyRequest($buyRequest)
    {
        if (isset($buyRequest['options']['ampromo_rule_id'])) {
            return (int)$buyRequest['options']['ampromo_rule_id'];
        }

        return null;
    }

    /**
     * @param AbstractItem|OrderItem $item
     * @return bool
     */
    public function isPromoItem($item)
    {
        if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            return false;
        }

        return $this->getRuleId($item) !== null;
    }

    /**
     * @param AbstractItem $item
     * @return mixed
     */
    public function getItemSku(AbstractItem $item)
    {
        $productType = $item->getProductType();
        if ($productType == Configurable::TYPE_CODE) {
            return $item->getProduct()->getData('sku');
        }

        return $item->getSku();
    }
}
