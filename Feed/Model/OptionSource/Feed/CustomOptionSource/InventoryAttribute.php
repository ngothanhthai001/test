<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class InventoryAttribute implements CustomOptionSourceInterface
{
    /**
     * @var Utils\ArrayCustomizer
     */
    private $arrayCustomizer;

    public function __construct(
        Utils\ArrayCustomizer $arrayCustomizer
    ) {
        $this->arrayCustomizer = $arrayCustomizer;
    }

    public function getOptions(): array
    {
        $attributes = [
            'qty' => __('Qty'),
            'is_in_stock' => __('Is In Stock')
        ];

        return $this->arrayCustomizer->customizeArray($attributes, ExportProduct::PREFIX_INVENTORY_ATTRIBUTE);
    }
}
