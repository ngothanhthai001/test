<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

namespace Amasty\PromoReports\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DateRange implements OptionSourceInterface
{
    /**
     * Data range
     */
    public const LAST_DAY = 1;
    public const LAST_WEEK = 7;
    public const LAST_MONTH = 30;
    public const OVERALL = 'Overall';
    public const CUSTOM = 0;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::LAST_DAY,
                'label' => __('Today')
            ],
            [
                'value' => self::LAST_WEEK,
                'label' => __('Last 7 days')
            ],
            [
                'value' => self::LAST_MONTH,
                'label' => __('Last 30 days')
            ],
            [
                'value' => self::OVERALL,
                'label' => __('Overall')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom')
            ],
        ];
    }
}
