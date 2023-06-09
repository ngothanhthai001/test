<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

namespace Amasty\PromoReports\Model\ResourceModel;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\PromoReports\Api\Data\ReportInterface;
use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;

class Report extends AbstractReport
{
    public const REPORT_PROMO_FLAG_CODE = 'report_amasty_promo_aggregated';

    public const AMPROMO_ITEM_EXPR = "order_item.product_options LIKE '%ampromo_rule_id%'";

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_ampromo_daily_reports', 'id');
    }

    /**
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws \Exception
     */
    public function aggregate($from = null, $to = null)
    {
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales_order'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            $periodExpr = $this->convertDatesToCurrentTimezone($from, $to);
            $this->aggregateDataByPeriod($periodExpr, $subSelect);
            $this->_setFlagData(self::REPORT_PROMO_FLAG_CODE);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return \Zend_Db_Expr
     */
    public function convertDatesToCurrentTimezone($from = null, $to = null)
    {
        $connection = $this->getConnection();

        return $connection->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                ['order' => $this->getTable('sales_order')],
                'order.created_at',
                $from,
                $to
            )
        );
    }

    /**
     * @param \Zend_Db_Expr $periodExpr
     * @param Select $subSelect
     */
    public function aggregateDataByPeriod($periodExpr, $subSelect)
    {
        $connection = $this->getConnection();
        $selectByOrderItem = $this->prepareSelectByOrderItem($periodExpr);
        $selectByOrder = $this->prepareSelectByOrder($selectByOrderItem);

        $select = $connection->select();
        $select->group(['period', 'store_id', 'customer_group_id']);
        $columns = [
            ReportInterface::PERIOD => 'by_order_grouped.period',
            ReportInterface::STORE_ID => 'by_order_grouped.store_id',
            ReportInterface::CUSTOMER_GROUP_ID => $connection->getIfNullSql('by_order_grouped.customer_group_id'),
            ReportInterface::TOTAL_SALES => new \Zend_Db_Expr('SUM(by_order_grouped.total_sales_with_promo)'),
            ReportInterface::ORDERS_COUNT => new \Zend_Db_Expr('SUM(by_order_grouped.orders_count_with_promo)'),
            ReportInterface::AVG_WITH_PROMO => new \Zend_Db_Expr(
                'ROUND(SUM(by_order_grouped.total_sales_with_promo) / SUM(by_order_grouped.items_per_order), 2)'
            ),
            ReportInterface::AVG_WITHOUT_PROMO => new \Zend_Db_Expr(
                'ROUND(SUM(by_order_grouped.total_sales_without_promo) / ' .
                'SUM(IF(by_order_grouped.total_sales_without_promo, 1, 0)), 2)'
            ),
            ReportInterface::PROMO_ITEMS_PER_ORDER => new \Zend_Db_Expr('SUM(by_order_grouped.items_per_order)')
        ];

        $select->from(
            ['by_order_grouped' => $selectByOrder],
            $columns
        );

        if ($subSelect !== null) {
            $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
        }

        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);
    }

    /**
     * @param \Zend_Db_Expr $periodExpr
     * @return Select
     */
    private function prepareSelectByOrderItem($periodExpr)
    {
        $connection = $this->getConnection();
        $select = $connection->select();

        $columns = [
            ReportInterface::PERIOD => $periodExpr,
            ReportInterface::STORE_ID => 'order.store_id',
            ReportInterface::CUSTOMER_GROUP_ID => 'order.customer_group_id',
            'order_id' => 'order.entity_id',  // necessary for grouping on the next level
            ReportInterface::TOTAL_SALES =>
                new \Zend_Db_Expr('IF(' . self::AMPROMO_ITEM_EXPR . ', order.base_grand_total, 0)'),
            'total_sales_without_promo' =>
                new \Zend_Db_Expr('IF(' . self::AMPROMO_ITEM_EXPR . ', 0, order.base_grand_total)'),
            ReportInterface::ORDERS_COUNT => new \Zend_Db_Expr('IF(' . self::AMPROMO_ITEM_EXPR . ', 1, 0)'),
            ReportInterface::PROMO_ITEMS_PER_ORDER =>
                new \Zend_Db_Expr('IF(' . self::AMPROMO_ITEM_EXPR . ', order_item.qty_ordered, 0)'),
            'items_per_order_without_promo' =>
                new \Zend_Db_Expr('IF(' . self::AMPROMO_ITEM_EXPR . ', 0, order_item.qty_ordered)'),
        ];

        $select->from(
            ['order' => $this->getTable('sales_order')],
            $columns
        )->joinInner(
            ['order_item' => $this->getTable('sales_order_item')],
            'order_item.order_id = order.entity_id',
            []
        )->where(
            'order.state != ?',
            \Magento\Sales\Model\Order::STATE_CANCELED
        )->where(
            'order_item.product_type = ?',
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        );

        return $select;
    }

    /**
     * @param Select $selectByOrderItem
     * @return Select
     */
    private function prepareSelectByOrder($selectByOrderItem)
    {
        $connection = $this->getConnection();
        $select = $connection->select();

        $select->group(['by_item_grouped.order_id']);

        $columns = [
            ReportInterface::PERIOD => 'by_item_grouped.period',
            ReportInterface::STORE_ID => 'by_item_grouped.store_id',
            ReportInterface::CUSTOMER_GROUP_ID => 'by_item_grouped.customer_group_id',
            ReportInterface::TOTAL_SALES => new \Zend_Db_Expr('MAX(by_item_grouped.total_sales_with_promo)'),
            // `total_sales_without_promo` it is the order base grand total where promo items are not used
            'total_sales_without_promo' => new \Zend_Db_Expr(
                'IF(SUM(by_item_grouped.items_per_order), 0, MAX(by_item_grouped.total_sales_without_promo))'
            ),
            ReportInterface::ORDERS_COUNT => new \Zend_Db_Expr('MAX(by_item_grouped.orders_count_with_promo)'),
            ReportInterface::PROMO_ITEMS_PER_ORDER => new \Zend_Db_Expr(
                'SUM(by_item_grouped.items_per_order) / MAX(by_item_grouped.orders_count_with_promo)'
            ),
            'items_per_order_without_promo' => new \Zend_Db_Expr(
                'IF(SUM(by_item_grouped.items_per_order), 0, SUM(by_item_grouped.items_per_order_without_promo))'
            )
        ];

        $select->from(
            ['by_item_grouped' => $selectByOrderItem],
            $columns
        );

        return $select;
    }

    /**
     * Set filters to load statistics data
     *
     * @param int|null $store
     * @param int|null $customerGroup
     * @param string|int|\DateTime|array|null $dateFrom
     * @param string|int|\DateTime|array|null $dateTo
     *
     * @return Select
     */
    public function loadStatisticsConditions($store = null, $customerGroup = null, $dateFrom = null, $dateTo = null)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $select = $connection->select()
            ->from(['main_table' => $table]);

        $this->addFilters($select, $store, $customerGroup, $dateFrom, $dateTo);

        $select->where('main_table.average_total_with_promo != ?', null);

        return $select;
    }

    /**
     * Return total information by select
     *
     * @param Select $select
     *
     * @return array
     */
    public function getTotalDataBySelect($select)
    {
        $connection = $this->getConnection();

        $select
            ->reset(Select::COLUMNS)
            ->columns([
                ReportInterface::TOTAL_SALES => 'SUM(total_sales_with_promo)',
                ReportInterface::ORDERS_COUNT => 'SUM(orders_count_with_promo)',
                ReportInterface::AVG_WITH_PROMO =>
                    'ROUND(SUM(total_sales_with_promo) / SUM(orders_count_with_promo), 2)',
                ReportInterface::PROMO_ITEMS_PER_ORDER =>
                    'ROUND((SUM(items_per_order) / SUM(orders_count_with_promo)), 2)'
            ]);

        return $connection->fetchRow($select);
    }

    /**
     * @param int|string|null $store
     * @param int|string|null $customerGroup
     * @param string|int|\DateTime|array|null $dateFrom
     * @param string|int|\DateTime|array|null $dateTo
     * @return array
     */
    public function loadAverageCartData($store = null, $customerGroup = null, $dateFrom = null, $dateTo = null)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->group(['YEAR(main_table.period)']);

        $columns = [
            ReportInterface::PERIOD => new \Zend_Db_Expr('YEAR(main_table.period)'),
            ReportInterface::AVG_WITH_PROMO => new \Zend_Db_Expr(
                'ROUND(SUM(main_table.total_sales_with_promo) / '
                    . 'SUM(main_table.orders_count_with_promo), 2)'
            ),
            ReportInterface::AVG_WITHOUT_PROMO => new \Zend_Db_Expr(
                'ROUND(SUM(main_table.average_total_without_promo) / '
                . 'SUM(IF(main_table.average_total_without_promo > 0, 1, 0)), 2)'
            ),
        ];

        $select->from(
            ['main_table' => $this->getMainTable()],
            $columns
        );

        $this->addFilters($select, $store, $customerGroup, $dateFrom, $dateTo);

        return $connection->fetchAll($select);
    }

    /**
     * @return int
     */
    public function getCountActiveFreeGiftRules(): int
    {
        $promoSimpleActions = [
            GiftRuleInterface::SAME_PRODUCT,
            GiftRuleInterface::PER_PRODUCT,
            GiftRuleInterface::WHOLE_CART,
            GiftRuleInterface::SPENT,
            GiftRuleInterface::EACHN
        ];

        $connection = $this->getConnection();
        $select = $connection->select();

        $select->from(['salesrule' => $this->getTable('salesrule')], ['COUNT(is_active)']);

        $select->where('salesrule.is_active = ?', 1)
            ->where('salesrule.simple_action IN (?)', $promoSimpleActions);

        return (int)$connection->fetchOne($select);
    }

    /**
     * @param Select $select
     * @param int|string|null $store
     * @param int|string|null $customerGroup
     * @param string|int|\DateTime|array|null $dateFrom
     * @param string|int|\DateTime|array|null $dateTo
     */
    private function addFilters(Select $select, $store = null, $customerGroup = null, $dateFrom = null, $dateTo = null)
    {
        if ($store != null) {
            $select->where('main_table.store_id =?', $store);
        }

        if ($customerGroup != null) {
            $select->where('main_table.customer_group_id =?', $customerGroup);
        }

        if ($dateFrom && $dateTo) {
            $select->where('main_table.period BETWEEN \'' . $dateFrom . '\' AND \'' . $dateTo . '\'');
        }
    }
}
