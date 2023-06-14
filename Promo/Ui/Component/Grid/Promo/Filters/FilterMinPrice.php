<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid\Promo\Filters;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class FilterMinPrice implements AddFilterToCollectionInterface
{
    /**
     * @param string $field
     * @param string[]|null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null): void
    {
        if (isset($condition['gteq'])) {
            $collection->getSelect()->where(
                'price_index.min_price >= ?',
                (float)$condition['gteq']
            );
        }
        if (isset($condition['lteq'])) {
            $collection->getSelect()->where(
                'price_index.min_price <= ?',
                (float)$condition['lteq']
            );
        }
    }
}
