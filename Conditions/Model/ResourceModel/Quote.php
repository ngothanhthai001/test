<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Advanced Conditions for Magento 2
 */

namespace Amasty\Conditions\Model\ResourceModel;

use Amasty\Conditions\Api\Data\QuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    public const TABLE_NAME = 'amasty_conditions_quote';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QuoteInterface::ITEM_ID);
    }
}
