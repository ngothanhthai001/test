<?php

namespace Amasty\PromoReports\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DateRange implements OptionSourceInterface
{
    /**
     * Data range
     */
    const LAST_DAY = 1;
    const LAST_WEEK = 7;
    const LAST_MONTH = 30;
    const OVERALL = 'Overall';
    const CUSTOM = 0;

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
