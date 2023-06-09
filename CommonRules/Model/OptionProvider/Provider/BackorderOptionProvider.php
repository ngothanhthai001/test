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
class BackorderOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    public const ALL_ORDERS = 0;
    public const BACKORDERS_ONLY = 1;
    public const NON_BACKORDERS = 2;

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
                ['value' => self::ALL_ORDERS, 'label' => __('All orders')],
                ['value' => self::BACKORDERS_ONLY, 'label' => __('Backorders only')],
                ['value' => self::NON_BACKORDERS, 'label' => __('Non backorders')]
            ];
        }

        return $this->options;
    }
}
