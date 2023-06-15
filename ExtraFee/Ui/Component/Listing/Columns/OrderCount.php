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

namespace Mageplaza\ExtraFee\Ui\Component\Listing\Columns;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class OrderCount
 * @package Mageplaza\ExtraFee\Ui\Component\Listing\Columns
 */
class OrderCount extends Column
{
    /**
     * @var OrderCollection
     */
    protected $orderCollection;

    /**
     * @var Json
     */
    protected $json;

    /**
     * OrderCount constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderCollection $orderCollection
     * @param Json $json
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderCollection $orderCollection,
        Json $json,
        array $components = [],
        array $data = []
    ) {
        $this->orderCollection = $orderCollection;
        $this->json            = $json;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items']) && is_array($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get customer group name
     *
     * @param array $item
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function prepareItem($item)
    {
        $ruleId          = $item['rule_id'];
        $orderCollection = $this->orderCollection->create()
            ->addFieldToFilter('status', ['nin' => ['pending', 'canceled', 'closed']]);
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
                    if ($id == $ruleId) {
                        $totalCount++;
                        break;
                    }
                }
            }
        }

        return $totalCount;
    }
}
