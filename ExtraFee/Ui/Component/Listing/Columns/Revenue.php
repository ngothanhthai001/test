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
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\ExtraFee\Helper\Data;

/**
 * Class Revenue
 * @package Mageplaza\ExtraFee\Ui\Component\Listing\Columns
 */
class Revenue extends Column
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
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Revenue constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderCollection $orderCollection
     * @param PriceHelper $priceHelper
     * @param Data $helper
     * @param Json $json
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderCollection $orderCollection,
        PriceHelper $priceHelper,
        Data $helper,
        Json $json,
        array $components = [],
        array $data = []
    ) {
        $this->orderCollection = $orderCollection;
        $this->json            = $json;
        $this->priceHelper     = $priceHelper;
        $this->helper = $helper;

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
            ->addFieldToFilter('status', ['nin' => ['pending', 'canceled']]);
        $total           = 0;

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

                $total += $totalInvoice - $totalCreditmemo;
            }
        }

        $total = ($total > 0) ? $total : 0;

        return $this->priceHelper->currency($total, true, false);
    }
}
