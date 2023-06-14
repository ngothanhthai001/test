<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\ResourceModel\PromoItem;

use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Amasty\Promo\Model\ResourceModel\PromoItem;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(PromoItemData::class, PromoItem::class);
    }
}
