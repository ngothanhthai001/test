<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Payment Restrictions for Magento 2
 */

namespace Amasty\Payrestriction\Setup\Patch\Data;

use Amasty\Base\Setup\SerializedFieldDataConverter;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConvertSerializeToJson implements DataPatchInterface
{
    /**
     * @var SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    public function __construct(
        SerializedFieldDataConverter $fieldDataConverter
    ) {
        $this->fieldDataConverter = $fieldDataConverter;
    }

    public function apply(): void
    {
        $this->fieldDataConverter->convertSerializedDataToJson(
            'am_payrestriction_rule',
            'rule_id',
            ['conditions_serialized']
        );
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
