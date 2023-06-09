<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Payment Restrictions for Magento 2
 */

namespace Amasty\Payrestriction\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    public const ACTIVE  = 1;
    public const INACTIVE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive')
        ];

        return $options;
    }
}
