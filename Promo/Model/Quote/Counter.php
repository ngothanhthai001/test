<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\Quote;

use Amasty\Promo\Api\Data\CounterInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Counter For Promo Items In Quote
 */
class Counter extends AbstractSimpleObject implements CounterInterface
{
    /**
     * @return int
     */
    public function getSelectedCount(): int
    {
        return (int)$this->_get(self::KEY_SELECTED);
    }

    /**
     * @return int
     */
    public function getAvailableCount(): int
    {
        return (int)$this->_get(self::KEY_AVAILABLE);
    }
}
