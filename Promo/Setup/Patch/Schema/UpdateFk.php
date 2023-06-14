<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Setup\Patch\Schema;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

class UpdateFk implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        MetadataPool $metadata,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->metadata = $metadata;
    }

    /**
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    public function apply(): void
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->moduleDataSetup->getConnection();
        $amruleTableName = $this->moduleDataSetup->getTable('amasty_ampromo_rule');
        $salesruleTableName = $this->moduleDataSetup->getTable('salesrule');
        $foreignKeys = $adapter->getForeignKeys($amruleTableName);
        $linkField = $this->metadata->getMetadata(RuleInterface::class)->getLinkField();
        if ($foreignKeys) {
            foreach ($foreignKeys as $key) {
                if ($key['COLUMN_NAME'] == GiftRuleInterface::SALESRULE_ID && $key['REF_COLUMN_NAME'] != $linkField) {
                    $this->setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName);
                    $adapter->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
                    $adapter->addForeignKey(
                        $adapter->getForeignKeyName(
                            $amruleTableName,
                            GiftRuleInterface::SALESRULE_ID,
                            $salesruleTableName,
                            $linkField
                        ),
                        $amruleTableName,
                        GiftRuleInterface::SALESRULE_ID,
                        $salesruleTableName,
                        $linkField
                    );
                }
            }
        } else {
            $adapter->addForeignKey(
                $adapter->getForeignKeyName(
                    $amruleTableName,
                    GiftRuleInterface::SALESRULE_ID,
                    $salesruleTableName,
                    $linkField
                ),
                $amruleTableName,
                GiftRuleInterface::SALESRULE_ID,
                $salesruleTableName,
                $linkField
            );
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $amruleTableName
     * @param string $salesruleTableName
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    private function setRowIdInsteadRuleId(
        AdapterInterface $adapter,
        string $amruleTableName,
        string $salesruleTableName
    ): void {
        $select = $adapter->select()
            ->from(
                $amruleTableName,
                [
                    GiftRuleInterface::SKU,
                    GiftRuleInterface::TYPE,
                    GiftRuleInterface::ITEMS_DISCOUNT,
                    GiftRuleInterface::MINIMAL_ITEMS_PRICE,
                    GiftRuleInterface::APPLY_TAX,
                    GiftRuleInterface::APPLY_SHIPPING
                ]
            )->joinInner(
                ['salesrule' => $salesruleTableName],
                'salesrule.rule_id = ' . $amruleTableName . '.salesrule_id',
                ['salesrule_id' => 'salesrule.row_id']
            )->setPart('disable_staging_preview', true);
        $amRules = $adapter->fetchAll($select);
        if (!empty($amRules)) {
            $adapter->truncateTable($amruleTableName);
            $adapter->insertMultiple($amruleTableName, $amRules);
        }
    }
    
    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
