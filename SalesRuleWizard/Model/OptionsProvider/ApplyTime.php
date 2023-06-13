<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Sales Rules Wizard for Magento 2 (System)
 */

namespace Amasty\SalesRuleWizard\Model\OptionsProvider;

class ApplyTime implements \Magento\Framework\Data\OptionSourceInterface
{
    public const FIRST_TIME = 'first';
    public const EVERY_TIME = 'every';
    public const LIMIT_TIME = 'limit';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIRST_TIME, 'label' => __('Only First Time when conditions are met')],
            ['value' => self::EVERY_TIME, 'label' => __('Every Time when conditions are met')],
            ['value' => self::LIMIT_TIME, 'label' => __('Every Time with Limit when conditions are met')]
        ];
    }
}
