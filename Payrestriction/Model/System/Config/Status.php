<?php

namespace Amasty\Payrestriction\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const ACTIVE  = 1;
    const INACTIVE = 0;

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
