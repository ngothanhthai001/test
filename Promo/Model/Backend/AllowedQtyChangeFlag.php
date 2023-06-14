<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\Backend;

class AllowedQtyChangeFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function set(bool $flag): void
    {
        $this->flag = $flag;
    }

    public function get(): bool
    {
        return $this->flag;
    }
}
