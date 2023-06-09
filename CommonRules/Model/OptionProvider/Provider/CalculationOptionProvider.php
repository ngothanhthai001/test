<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\OptionProvider\Provider;

class CalculationOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    public const CALC_REPLACE = 0;
    public const CALC_ADD     = 1;
    public const CALC_DEDUCT  = 2;
    public const CALC_REPLACE_PRODUCT = 3;

    /**
     * @var array|null
     */
    protected $options;

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'label' => __('Replace'),
                    'value' => self::CALC_REPLACE
                ],
                [
                    'label' => __('Surcharge'),
                    'value' => self::CALC_ADD
                ],
                [
                    'label' => __('Discount'),
                    'value' => self::CALC_DEDUCT
                ],
                [
                    'label' => __('Partial Replace'),
                    'value' => self::CALC_REPLACE_PRODUCT
                ]
            ];
        }

        return $this->options;
    }
}
