<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GiftRepresentationMode implements OptionSourceInterface
{
    public const SHOW_FULL_PRICE = 0;

    public const SHOW_ZERO_PRICE = 1;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::SHOW_FULL_PRICE => __('Product with 100% Discount'),
            self::SHOW_ZERO_PRICE => __('Product with $0 Price'),
        ];
    }
}
