<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Multiple Coupons for Magento 2
 */

namespace Amasty\Coupons\Model\ResourceModel;

use Amasty\Coupons\Api\Data\RuleInterface;

/**
 * Resource model for Rule object.
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_coupons_same_rule';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, RuleInterface::ENTITY_ID);
    }
}
