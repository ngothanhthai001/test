<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */
namespace Amasty\Promo\Model\ResourceModel;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesRule\Api\Data\RuleInterface;

class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_ampromo_rule';

    public const APPLY_TO_TAX_COLUMN_NAME = 'apply_tax';
    public const APPLY_TO_SHIPPING_COLUMN_NAME = 'apply_shipping';

    public const PROMO_RULES = [
        GiftRuleInterface::PER_PRODUCT,
        GiftRuleInterface::SAME_PRODUCT,
        GiftRuleInterface::SPENT,
        GiftRuleInterface::WHOLE_CART,
        GiftRuleInterface::EACHN,
    ];

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        Context $context,
        MetadataPool $metadata,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadata = $metadata;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_ampromo_rule', GiftRuleInterface::ENTITY_ID);
    }

    /**
     * @param $ruleIds
     * @return bool
     */
    public function isApplyTax($ruleIds)
    {
        $isApplyForRules = $this->isApplicable($ruleIds, self::APPLY_TO_TAX_COLUMN_NAME);

        return count($isApplyForRules) == 1 ? (bool) $isApplyForRules[0][self::APPLY_TO_TAX_COLUMN_NAME] : false;
    }

    /**
     * @param $ruleIds
     * @return bool
     */
    public function isApplyShipping($ruleIds)
    {
        $isApplyForRules = $this->isApplicable($ruleIds, self::APPLY_TO_SHIPPING_COLUMN_NAME);

        return count($isApplyForRules) == 1 ? (bool) $isApplyForRules[0][self::APPLY_TO_SHIPPING_COLUMN_NAME] : true;
    }

    /**
     * @param $ruleIds
     * @param $column
     * @return array
     */
    public function isApplicable($ruleIds, $column)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), $column)
            ->where('salesrule_id in (?)', $ruleIds)
            ->group($column);

        return $connection->fetchAll($select);
    }

    public function disablePromoRules(): void
    {
        $mainTable = $this->getMainTable();
        $salesRuleTable = $this->getTable('salesrule');
        $linkField = $this->metadata->getMetadata(RuleInterface::class)->getLinkField();
        $select = $this->getConnection()->select()
            ->from($mainTable, ['salesrule_id'])
            ->join(
                [$salesRuleTable],
                $mainTable . '.salesrule_id = ' . $salesRuleTable . '.' . $linkField,
                ['is_active']
            )
            ->where('is_active = ?', 1)
            ->where('simple_action IN (?)', self::PROMO_RULES);

        $ruleIds = $this->getConnection()->fetchCol($select);
        if (!empty($ruleIds)) {
            $this->getConnection()->update($salesRuleTable, ['is_active' => 0], [$linkField . ' IN (?)' => $ruleIds]);
        }
    }
}
