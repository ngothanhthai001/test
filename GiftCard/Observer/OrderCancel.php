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

namespace Mageplaza\GiftCard\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mageplaza\GiftCard\Helper\Data as Helper;
use Mageplaza\GiftCard\Model\GiftCard\Action;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\Transaction\Action as TransactionAction;
use Mageplaza\GiftCard\Model\TransactionFactory;
use Psr\Log\LoggerInterface;
use Mageplaza\GiftCard\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Class OrderCancel
 * @package Mageplaza\GiftCard\Observer
 */
class OrderCancel implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GiftCardFactory
     */
    protected $giftCardFactory;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * OrderCancel constructor.
     *
     * @param Helper $helper
     * @param GiftCardFactory $giftCardFactory
     * @param TransactionFactory $transactionFactory
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Helper $helper,
        GiftCardFactory $giftCardFactory,
        TransactionFactory $transactionFactory,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory
    ) {
        $this->_helper = $helper;
        $this->giftCardFactory = $giftCardFactory;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        $giftCards = $order->getMpGiftCards() ? Helper::jsonDecode($order->getMpGiftCards()) : [];
        foreach ($giftCards as $code => $amount) {
            try {
                $giftCard = $this->giftCardFactory->create()->loadByCode($code);
                if ($giftCard->getId()) {
                    $giftCard->addBalance($amount)
                        ->setAction(Action::ACTION_REVERT)
                        ->setActionVars(['order_increment_id' => $order->getIncrementId()])
                        ->save();
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        $giftCredit = $order->getBaseGiftCreditAmount();
        if (abs($giftCredit) > 0.0001) {
            try {
                $this->transactionFactory->create()
                    ->createTransaction(
                        TransactionAction::ACTION_REVERT,
                        abs($giftCredit),
                        $order->getCustomerId(),
                        $expiredAt = null,
                        ['order_increment_id' => $order->getIncrementId()]
                    );

                /** Process mageplaza_giftcard_transaction after revert **/
                $collection = $this->_collectionFactory->create();
                $collection->getSelect()
                    ->join(
                        ['cr' => $collection->getTable('mageplaza_giftcard_credit')],
                        'main_table.action = 2 AND main_table.credit_id = cr.credit_id AND cr.customer_id = ' . $order->getCustomerId(),
                        ['customer_id']
                    )->order(['expired_at ASC', 'transaction_id DESC']);

                foreach ($collection as $redeemed)
                {
                    if($redeemed->getStatus() != 5) {
                        if(abs($giftCredit) > 0.0001) {
                            $totalGiftCredit = abs($giftCredit) + abs($redeemed->getCurrentAmount());
                            if($totalGiftCredit <= abs($redeemed->getOldCurrentAmount())){
                                $redeemed->setCurrentAmount($totalGiftCredit);
                                $giftCredit = 0.0000;
                            }else{
                                $redeemed->setCurrentAmount(abs($redeemed->getOldCurrentAmount()));
                                $giftCredit = $totalGiftCredit - abs($redeemed->getOldCurrentAmount());
                            }
                        }
                    }
                }
                $collection->save();

            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
