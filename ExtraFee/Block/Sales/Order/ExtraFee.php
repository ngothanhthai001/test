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

namespace Mageplaza\ExtraFee\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Tax\Model\Config;
use Mageplaza\ExtraFee\Helper\Data;

/**
 * Class ExtraFee
 * @package Mageplaza\ExtraFee\Block\Sales\Order
 */
class ExtraFee extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * ExtraFee constructor.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent            = $this->getParentBlock();
        $order             = $this->getOrder();
        $store             = $order->getStore();
        $oriExtraFeeTotals = $this->helper->getExtraFeeTotals($order);
        $extraFeeTotals    = $this->helper->getObjectExtraFeeTotals($parent->getSource(), $order);

        if ($parent->getSource()->getEntityType() === 'order') {
            $extraFeeTotals = $oriExtraFeeTotals;
        }

        foreach ($extraFeeTotals as $extraFeeTotal) {
            if (!$extraFeeTotal['rf'] && $parent->getSource()->getEntityType() === 'creditmemo') {
                continue;
            }
            $totalIncl = new DataObject(
                [
                    'code'       => $extraFeeTotal['code'] . '_incl',
                    'value'      => $extraFeeTotal['value_incl_tax'],
                    'base_value' => $extraFeeTotal['base_value_incl_tax'],
                    'label'      => $extraFeeTotal['rule_label']
                        . ((strpos($extraFeeTotal['code'], 'auto') === false)
                            ? ' - ' . $extraFeeTotal['label'] : $extraFeeTotal['label']) . __(' (Incl.Tax)'),
                ]
            );

            if ($this->config->displaySalesShippingBoth($store)) {
                $totalExcl = new DataObject(
                    [
                        'code'       => $extraFeeTotal['code'],
                        'value'      => $extraFeeTotal['value_excl_tax'],
                        'base_value' => $extraFeeTotal['base_value'],
                        'label'      => $extraFeeTotal['rule_label']
                            . ((strpos($extraFeeTotal['code'], 'auto') === false)
                                ? ' - ' . $extraFeeTotal['label'] : $extraFeeTotal['label']) . __(' (Excl.Tax)'),
                    ]
                );
                $parent->addTotal($totalIncl, 'subtotal_incl');
                $parent->addTotal($totalExcl, 'subtotal_incl');
            } elseif ($this->config->displaySalesShippingInclTax($store)) {
                $parent->addTotal($totalIncl, 'subtotal_incl');
            } else {
                $parent->addTotal(new DataObject([
                    'code'       => $extraFeeTotal['code'],
                    'value'      => $extraFeeTotal['value_excl_tax'],
                    'base_value' => $extraFeeTotal['base_value'],
                    'label'      => $extraFeeTotal['rule_label']
                        . ((strpos($extraFeeTotal['code'], 'auto') === false)
                            ? ' - ' . $extraFeeTotal['label'] : $extraFeeTotal['label']),
                ]), 'subtotal_incl');
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if (!$source->getQuoteId()) {
            $source = $source->getOrder();
        }

        return $source;
    }
}
