<?php

namespace Amasty\Payrestriction\Plugin;


class ProductAttributes extends \Amasty\CommonRules\Plugin\ProductAttributes
{
    /**
     * ProductAttributes constructor.
     * @param \Amasty\Payrestriction\Model\ResourceModel\Rule $resourceTable
     */
    public function __construct(\Amasty\Payrestriction\Model\ResourceModel\Rule $resourceTable)
    {
        parent::__construct($resourceTable);
    }
}