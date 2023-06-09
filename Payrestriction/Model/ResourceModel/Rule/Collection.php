<?php

namespace Amasty\Payrestriction\Model\ResourceModel\Rule;

class Collection extends \Amasty\CommonRules\Model\ResourceModel\Rule\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Payrestriction\Model\Rule', 'Amasty\Payrestriction\Model\ResourceModel\Rule');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
