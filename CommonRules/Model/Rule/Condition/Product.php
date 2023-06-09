<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\Rule\Condition;

class Product extends \Magento\SalesRule\Model\Rule\Condition\Product
{
    /**
     * @return $this
     */
    protected function _prepareValueOptions()
    {
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');

        if ($selectReady && $hashedReady) {
            return $this;
        }

        parent::_prepareValueOptions();

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
        }

        return $this->_defaultOperatorInputByType;
    }
}
