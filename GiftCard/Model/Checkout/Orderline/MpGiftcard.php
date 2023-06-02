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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Model\Checkout\Orderline;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Helper\DataConverter;
use Klarna\Core\Helper\KlarnaConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Tax\Model\Calculation;
use Klarna\Core\Model\Checkout\Orderline\AbstractLine;
use Mageplaza\GiftCard\Helper\Checkout;

/**
 * Generate order line details for gift card
 */
class MpGiftcard extends AbstractLine
{
    /**
     * @var Checkout
     */
    protected $_helperCheckout;

    /**
     * Checkout item type
     */
    const ITEM_TYPE_GIFTCARD = 'gift_card';

    public function __construct(
        DataConverter $helper,
        Calculation $calculator,
        ScopeConfigInterface $config,
        DataObjectFactory $dataObjectFactory,
        KlarnaConfig $klarnaConfig,
        Checkout $helperCheckout
    ){
        $this->_helperCheckout = $helperCheckout;
        parent::__construct($helper, $calculator, $config, $dataObjectFactory, $klarnaConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(BuilderInterface $checkout)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $checkout->getObject();
        $totals = $quote->getTotals();

        if (is_array($totals)) {
            $giftcardValue = 0;
            $creditValue   = 0;
            $reference     = 'Gift Card';
            $title         = 'Mageplaza Gift Card';

            if(isset($totals['gift_card'])){
                $giftcardTotal       = $totals['gift_card'];
                $giftCardAmount      = $giftcardTotal->getValue();

                if ($giftCardAmount !== 0) {
                    $amount  = 0;
                    $giftCodeUsed = $this->_helperCheckout->getGiftCardsUsed($quote);

                    foreach ($giftCodeUsed as $value) {
                        $amount += $value;
                    }
                    $giftcardValue = -1 * $this->helper->toApiFloat($amount);
                }
            }

            if(isset($totals['gift_credit'])){
                $creditTotal  = $totals['gift_credit'];
                $creditAmount = $creditTotal->getValue();

                if($creditAmount !== 0){
                    $creditValue = $this->helper->toApiFloat($creditAmount);
                }
            }

            if($giftcardValue !== 0 || $creditValue !== 0){
                $totalAmount = $giftcardValue + $creditValue;

                $checkout->addData([
                    'mpgiftcard_unit_price'   => $totalAmount,
                    'mpgiftcard_tax_rate'     => 0,
                    'mpgiftcard_total_amount' => $totalAmount,
                    'mpgiftcard_tax_amount'   => 0,
                    'mpgiftcard_title'        => $title,
                    'mpgiftcard_reference'    => $reference
                ]);
            }
        }

        if (is_array($totals) && isset($totals['giftcardaccount'])) {
            $total = $totals['giftcardaccount'];
            $amount = $total->getValue();
            if ($amount !== 0) {
                $amount = $quote->getGiftCardsAmountUsed();
                $value = -1 * $this->helper->toApiFloat($amount);

                $checkout->addData([
                    'giftcardaccount_unit_price'   => $value,
                    'giftcardaccount_tax_rate'     => 0,
                    'giftcardaccount_total_amount' => $value,
                    'giftcardaccount_tax_amount'   => 0,
                    'giftcardaccount_title'        => $total->getTitle()->getText(),
                    'giftcardaccount_reference'    => $total->getCode()
                ]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if ($checkout->getGiftcardaccountTotalAmount()) {
            $checkout->addOrderLine([
                'type'             => self::ITEM_TYPE_GIFTCARD,
                'reference'        => $checkout->getGiftcardaccountReference(),
                'name'             => $checkout->getGiftcardaccountTitle(),
                'quantity'         => 1,
                'unit_price'       => $checkout->getGiftcardaccountUnitPrice(),
                'tax_rate'         => $checkout->getGiftcardaccountTaxRate(),
                'total_amount'     => $checkout->getGiftcardaccountTotalAmount(),
                'total_tax_amount' => $checkout->getGiftcardaccountTaxAmount(),
            ]);
        }

        if ($checkout->getMpgiftcardTotalAmount()) {
            $checkout->addOrderLine([
                'type'             => self::ITEM_TYPE_GIFTCARD,
                'reference'        => $checkout->getMpgiftcardReference(),
                'name'             => $checkout->getMpgiftcardTitle(),
                'quantity'         => 1,
                'unit_price'       => $checkout->getMpgiftcardUnitPrice(),
                'tax_rate'         => $checkout->getMpgiftcardTaxRate(),
                'total_amount'     => $checkout->getMpgiftcardTotalAmount(),
                'total_tax_amount' => $checkout->getMpgiftcardTaxAmount(),
            ]);
        }

        return $this;
    }
}
