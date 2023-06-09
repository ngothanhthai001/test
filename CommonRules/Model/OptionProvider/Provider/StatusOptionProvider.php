<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\OptionProvider\Provider;

/**
 * OptionProvider
 */
class StatusOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    public const ACTIVE  = 1;
    public const INACTIVE = 0;

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
                ['value' => self::ACTIVE, 'label' => __('Active')],
                ['value' => self::INACTIVE, 'label' => __('Inactive')],
            ];
        }

        return $this->options;
    }
}
