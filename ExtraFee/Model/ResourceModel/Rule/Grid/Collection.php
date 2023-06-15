<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Model\ResourceModel\Rule\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Store\Model\Store;
use Mageplaza\ExtraFee\Helper\Data;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayArea;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Psr\Log\LoggerInterface as Logger;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\ExtraFee\Model\ResourceModel\Rule\Grid
 */
class Collection extends SearchResult
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_extrafee_rule_grid_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'rule_grid_collection';

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod

    /**
     * @var RuleCollection
     */
    protected $ruleCollection;

    /**
     * @var OrderCollection
     */
    protected $orderCollection;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param RuleCollection $ruleCollection
     * @param OrderCollection $orderCollection
     * @param Data $helper
     * @param Json $json
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        RuleCollection $ruleCollection,
        OrderCollection $orderCollection,
        Data $helper,
        Json $json,
        EventManager $eventManager,
        $mainTable = 'mageplaza_extrafee_rule',
        $resourceModel = Rule::class
    ) {
        $this->ruleCollection  = $ruleCollection;
        $this->orderCollection = $orderCollection;
        $this->json            = $json;
        $this->helper = $helper;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'order_count') {
            $ruleIds = $this->filterTotalOrder($condition, 'order_count');
            if (count($ruleIds)) {
                return parent::addFieldToFilter('rule_id', ['in' => $ruleIds]);
            } else {
                $ruleIds = $this->ruleCollection->create()->getAllIds();

                return parent::addFieldToFilter('rule_id', ['nin' => $ruleIds]);
            }
        }
        if ($field == 'revenue') {
            $ruleIds = $this->filterTotalOrder($condition, 'revenue');
            if (count($ruleIds)) {
                return parent::addFieldToFilter('rule_id', ['in' => $ruleIds]);
            } else {
                $ruleIds = $this->ruleCollection->create()->getAllIds();

                return parent::addFieldToFilter('rule_id', ['nin' => $ruleIds]);
            }
        }
        if ($field === 'customer_groups_ids') {
            $field     = 'customer_groups';
            $condition = ['finset' => $condition['eq']];
        }
        if ($field === 'stores') {
            $field     = 'store_ids';
            $condition = [
                ['finset' => $condition['eq']],
                ['finset' => Store::DEFAULT_STORE_ID]
            ];
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return $this|Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field === 'area') {
            $values = [DisplayArea::CART_SUMMARY, DisplayArea::PAYMENT_METHOD, DisplayArea::SHIPPING_METHOD];
            $this->getSelect()->order(
                new Zend_Db_Expr('FIELD(main_table.area, ' . implode(',', $values) . ') ' . $direction)
            );
        }
        if ($field === 'order_count' || $field === 'revenue') {
            $ruleIds = $this->sortReportField($field, $direction);
            $this->getSelect()->order(
                new Zend_Db_Expr('FIELD(main_table.rule_id, ' . implode(',', $ruleIds) . ') ' . $direction)
            );
        } else {
            parent::setOrder($field, $direction); // TODO: Change the autogenerated stub
        }

        return $this;
    }

    /**
     * @param $rule
     *
     * @return int|mixed
     */
    protected function getRevenue($rule)
    {
        $orderCollection = $this->orderCollection->create()
            ->addFieldToFilter('status', ['nin' => ['pending', 'canceled']]);
        $ruleId          = $rule->getId();
        $revenue         = 0;

        foreach ($orderCollection as $order) {
            /** @var Order $order */
            $totalInvoice    = 0;
            $totalCreditmemo = 0;
            if ($order->getMpExtraFee()) {
                $invoiceCollection = $order->getInvoiceCollection();
                if ($invoiceCollection->getSize()) {
                    $totalInvoice = $this->helper->getReportTotal($invoiceCollection, $ruleId, 'invoice');
                }

                $creditmemoCollection = $order->getCreditmemosCollection();
                if ($creditmemoCollection->getSize()) {
                    $totalCreditmemo = $this->helper->getReportTotal($creditmemoCollection, $ruleId, 'creditmemo');
                }

                $revenue += $totalInvoice - $totalCreditmemo;
            }
        }

        $revenue = ($revenue > 0) ? $revenue : 0;

        return $revenue;
    }

    /**
     * @param $rule
     *
     * @return int
     */
    protected function getOrderCount($rule)
    {
        $orderCollection = $this->orderCollection->create()->addFieldToFilter('status',
            ['nin' => ['pending', 'canceled', 'closed']]);
        $totalCount      = 0;

        foreach ($orderCollection as $order) {
            if ($order->getMpExtraFee()) {
                $orderExtraFee = $this->json->unserialize($order->getMpExtraFee());
                if (!array_key_exists('totals', $orderExtraFee)) {
                    continue;
                }
                foreach ($orderExtraFee['totals'] as $total) {
                    $id = array_filter(preg_split("/\D+/", $total['code']));
                    $id = reset($id);
                    if ($id == $rule->getId()) {
                        $totalCount++;
                        break;
                    }
                }
            }
        }

        return $totalCount;
    }

    /**
     * @param $conditions
     * @param $type
     *
     * @return array
     */
    protected function filterTotalOrder($conditions, $type)
    {
        $ruleIds        = [];
        $ruleCollection = $this->ruleCollection->create();

        foreach ($ruleCollection as $rule) {
            $value = ($type == 'order_count') ? $this->getOrderCount($rule) : $this->getRevenue($rule);
            if (isset($conditions['gteq']) && isset($conditions['lteq'])) {
                if ($value <= $conditions['lteq'] && $value >= $conditions['gteq']) {
                    $ruleIds[] = $rule->getId();
                }
            } else {
                if (isset($conditions['gteq']) && $value >= $conditions['gteq']) {
                    $ruleIds[] = $rule->getId();
                }
                if (isset($conditions['lteq']) && $value <= $conditions['lteq']) {
                    $ruleIds[] = $rule->getId();
                }
            }
        }

        return $ruleIds;
    }

    /**
     * @param $type
     * @param $direction
     *
     * @return array
     */
    protected function sortReportField($type, $direction)
    {
        $data           = [];
        $ruleCollection = $this->ruleCollection->create();
        foreach ($ruleCollection as $rule) {
            $value                = ($type == 'order_count') ? $this->getOrderCount($rule) : $this->getRevenue($rule);
            $data[$rule->getId()] = $value;
        }

        $direction == 'asc' ? asort($data) : arsort($data);

        return array_keys($data);
    }
}
