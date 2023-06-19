<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AdminPermissions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Model\Config\Source;

/**
 * Class DaysOfWeek
 * @package Mageplaza\AdminPermissions\Model\Config\Source
 */
class DaysOfWeek extends AbstractSource
{
    const MONDAY    = 'Monday';
    const TUESDAY   = 'Tuesday';
    const WEDNESDAY = 'Wednesday';
    const THURSDAY  = 'Thursday';
    const FRIDAY    = 'Friday';
    const SATURDAY  = 'Saturday';
    const SUNDAY    = 'Sunday';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            ''              => __('Please Select'),
            self::MONDAY    => __('Monday'),
            self::TUESDAY   => __('Tuesday'),
            self::WEDNESDAY => __('Wednesday'),
            self::THURSDAY  => __('Thursday'),
            self::FRIDAY    => __('Friday'),
            self::SATURDAY  => __('Saturday'),
            self::SUNDAY    => __('Sunday'),
        ];
    }
}
