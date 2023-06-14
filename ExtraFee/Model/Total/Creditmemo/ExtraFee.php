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

namespace Mageplaza\ExtraFee\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Mageplaza\ExtraFee\Helper\Data;

/**
 * Class ExtraFee
 * @package Mageplaza\ExtraFee\Model\Total\Creditmemo
 */
class ExtraFee extends AbstractTotal
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ExtraFee constructor.
     *
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($data);
    }

    /**
     * Collect Creditmemo subtotal
     *
     * @param Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order          = $creditmemo->getOrder();
        $extraFeeTotals = $this->helper->getObjectExtraFeeTotals($creditmemo, $order);

        foreach ($extraFeeTotals as $fee) {
            if ($fee['rf']) {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $fee['value_incl_tax']);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $fee['base_value_incl_tax']);
                $creditmemo->setOdGrandTotal($creditmemo->getOdGrandTotal() + $fee['value_incl_tax']);
                $creditmemo->setOdBaseGrandTotal($creditmemo->getOdBaseGrandTotal() + $fee['base_value_incl_tax']);
            }
        }

        return $this;
    }
}
