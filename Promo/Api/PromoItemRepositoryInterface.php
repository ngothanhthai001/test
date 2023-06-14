<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Api;

use Amasty\Promo\Model\ItemRegistry\PromoItemsGroup;

interface PromoItemRepositoryInterface
{
    /**
     * @param int $quoteId
     * @return PromoItemsGroup
     */
    public function getItemsByQuoteId(int $quoteId): PromoItemsGroup;

    /**
     * @param int $quoteId
     * @return void
     */
    public function saveItems(int $quoteId): void;
}
