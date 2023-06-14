<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Api\Data;

/**
 * Counter For Promo Items In Quote
 *
 * @see \Amasty\Promo\Model\Quote\PromoItemCounter
 */
interface CounterInterface
{
    public const KEY_AVAILABLE = 'available';
    public const KEY_SELECTED = 'selected';

    /**
     * Return Promo Items Available Count
     * @return int
     */
    public function getAvailableCount(): int;

    /**
     * Return Promo Items Selected In Quote Count
     * @return int
     */
    public function getSelectedCount(): int;
}
