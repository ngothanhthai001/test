<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PromoItem extends AbstractDb
{
    public const TABLE_NAME = 'amasty_ampromo_items_storage';

    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    public function deleteByQuoteId($quoteId)
    {
        $this->getConnection()->delete($this->getTable(self::TABLE_NAME), ['quote_id  = (?)' => $quoteId]);
    }
}
