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

namespace Mageplaza\GiftCard\Model;

use Exception;
use IntlDateFormatter;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Helper\Data as DataHelper;
use Mageplaza\GiftCard\Helper\Email;
use Mageplaza\GiftCard\Model\GiftCard\Action as GiftCardAction;
use Mageplaza\GiftCard\Model\Transaction\Action;
use Mageplaza\GiftCard\Model\ResourceModel\Transaction\CollectionFactory;
use Zend_Db_Select;
use Mageplaza\GiftCard\Model\ResourceModel\History\Collection;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\Collection as GiftCardCollection;

/**
 * Class Transaction
 * @package Mageplaza\GiftCard\Model
 * @method getAction()
 */
class Transaction extends AbstractModel implements IdentityInterface
{
    /**
     * Cache
     */
    const CACHE_TAG = 'mageplaza_giftcard_transaction';

    /**
     * @var CreditFactory
     */
    protected $creditFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var GiftCard
     */
    protected $_giftCard;

    /**
     * @var HistoryFactory
     */
    private $_historyFactory;
    /**
     * @var GiftCardFactory
     */
    private $_giftCardFactory;

    /**
     * Transaction constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CreditFactory $creditFactory
     * @param CustomerFactory $customerFactory
     * @param DataHelper $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param GiftCard $giftCard
     * @param HistoryFactory $historyFactory
     * @param GiftCardFactory $giftCardFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        CreditFactory         $creditFactory,
        CustomerFactory       $customerFactory,
        DataHelper            $dataHelper,
        StoreManagerInterface $storeManager,
        GiftCard              $giftCard,
        HistoryFactory        $historyFactory,
        GiftCardFactory       $giftCardFactory,
        AbstractResource      $resource = null,
        AbstractDb            $resourceCollection = null,
        array                 $data = []
    )
    {
        $this->creditFactory = $creditFactory;
        $this->customerFactory = $customerFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_giftCard = $giftCard;
        $this->_historyFactory  = $historyFactory;
        $this->_giftCardFactory  = $giftCardFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Transaction::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get gift card transaction action label
     *
     * @param null $action
     *
     * @return Phrase|string
     */
    public function getActionLabel($action = null)
    {
        if ($action === null) {
            $action = $this->getAction();
        }

        $allStatus = Action::getOptionArray();

        return isset($allStatus[$action]) ? $allStatus[$action] : __('Undefined');
    }

    /**
     * @param $action
     * @param $amount
     * @param $customer
     * @param null $expiredAt
     * @param $extraContent
     *
     * @return Credit
     * @throws LocalizedException
     */
    public function createTransaction($action, $amount, $customer, $expiredAt, $extraContent, $status = null)
    {
        $credit = $this->prepareTransaction($action, $amount, $customer, $expiredAt, $extraContent, $status);

        $this->getResource()->createTransaction([$this, $credit]);

        return $credit;
    }

    /**
     * @param Customer $customer
     * @param GiftCard $giftCard
     *
     * @return $this
     * @throws LocalizedException
     */
    public function redeemGiftCard($customer, $giftCard)
    {
        if (!$giftCard->isActive()) {
            throw new LocalizedException(__(
                'Cannot redeem gift card. Gift Card "%1" does not available.',
                $giftCard->getCode()
            ));
        }

        $credit = $this->prepareTransaction(
            Action::ACTION_REDEEM,
            $giftCard->getBalance(),
            $customer,
            $giftCard->getExpiredAt(),
            ['code' => $giftCard->getCode()]
        );

        $giftCard->setBalance(0)
            ->setActionVars(['auth' => $customer->getName(), 'customer_id' => $customer->getId()])
            ->setAction(GiftCardAction::ACTION_REDEEM);

        $this->getResource()->createTransaction([$this, $credit, $giftCard]);

        return $this;
    }

    /**
     * @param $action
     * @param $amount
     * @param Customer|int $customer
     * @param null $expiredAt
     * @param $extraContent
     * @param null $status
     * @return Credit
     * @throws LocalizedException
     */
    protected function prepareTransaction($action, $amount, &$customer, $expiredAt, $extraContent, $status = null)
    {
        if (is_numeric($customer)) {
            $customer = $this->customerFactory->create()->load($customer);
        }
        if (!$customer->getId()) {
            throw new LocalizedException(__('Customer does not exists.'));
        }

        $credit = $this->creditFactory->create()->load($customer->getId(), 'customer_id');
        if (!$credit->getId()) {
            try {
                $credit->setCustomerId($customer->getId())->save();
            } catch (Exception $e) {
                throw new LocalizedException(__('Cannot save customer balance.'));
            }
        }

        $balanceAfterChange = $credit->getBalance() + $amount;
        if ($balanceAfterChange < 0) {
            throw new LocalizedException(__('Customer balance is not enough.'));
        }

        $credit->setBalance($balanceAfterChange);

        //Prepare information for send mail to customer that used balance to place order
        if ((int)$action === Action::ACTION_SPEND) {
            $credit->setAmount($amount);
            $credit->setAction(Action::ACTION_SPEND);
            $credit->setOrderIncrementId($extraContent['order_increment_id']);
            $credit->setCustomer($customer);
        }

        $this->setData([
            'credit' => $credit,
            'credit_id' => $credit->getId(),
            'balance' => $balanceAfterChange,
            'amount' => $amount,
            'current_amount' => $amount > 0 ? $amount : 0,
            'action' => $action,
            'extra_content' => DataHelper::jsonEncode($extraContent),
            'expired_at' => $expiredAt,
            'status' => $status
        ]);

        return $credit;
    }

    /**
     * Get Customer transaction
     *
     * @param $customerId
     *
     * @return array
     * @throws Exception
     */
    public function getTransactionsForCustomer($customerId)
    {
        $transactionList = [];

        $transactions = $this->getCollection()->setOrder('created_at', 'desc');
        $transactions->getSelect()
            ->join(
                ['cr' => $transactions->getTable('mageplaza_giftcard_credit')],
                'main_table.credit_id = cr.credit_id AND cr.customer_id = ' . $customerId,
                ['customer_id']
            );

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $transaction->addData([
                'created_at_formatted' => $this->_dataHelper->formatDate(
                    $transaction->getCreatedAt(),
                    IntlDateFormatter::MEDIUM
                ),
                'action_label' => $transaction->getActionLabel(),
                'amount_formatted' => $this->_dataHelper->convertPrice($transaction->getAmount()),
                'action_detail' => Action::getActionLabel(
                    $transaction->getAction(),
                    $transaction->getExtraContent()
                )
            ]);

            $transactionList[] = $transaction->getData();
        }

        return $transactionList;
    }

    /**
     * Get Customer transaction
     *
     * @param $customerId
     *
     * @return array
     * @throws Exception
     */
    public function getRedeemedForCustomer($customerId)
    {
        $redeemedList = [];

        $redeemeds = $this->getCollection();
        $redeemeds->getSelect()
            ->join(
                ['cr' => $redeemeds->getTable('mageplaza_giftcard_credit')],
                'main_table.current_amount > 0.0001 AND main_table.credit_id = cr.credit_id AND main_table.action != 3 AND main_table.action != 5 AND cr.customer_id = ' . $customerId,
                ['customer_id']
            )->order('transaction_id');

        foreach ($redeemeds as $item) {
            $code = "";
            if (!is_array($item->getExtraContent())) {
                $extraContent = DataHelper::jsonDecode($item->getExtraContent());
            }
            if (isset($extraContent['code'])) {
                $code = $extraContent['code'];
            }

            $expiredAt = __('Indefinite');
            if ($item->getExpiredAt() != null) {
                $expiredAt = $this->_dataHelper->formatDate(
                    $item->getExpiredAt(),
                    IntlDateFormatter::MEDIUM
                );
            }
            $item->addData([
                'expired_at_formatted' => $expiredAt,
                'action_label' => $item->getActionLabel(),
                'balance_formatted' => $this->_dataHelper->convertPrice($item->getBalance()),
                'amount_formatted' => $this->_dataHelper->convertPrice($item->getAmount()),
                'current_amount_formatted' => $this->_dataHelper->convertPrice($item->getCurrentAmount()),
                'code' => $code,
                'hidden_code' => $this->_giftCard->getHiddenCode($code),
            ]);

            $redeemedList[] = $item->getData();
        }

        return $redeemedList;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function afterSave()
    {
        parent::afterSave();

        /** @var Email $emailHelper */
        $emailHelper = $this->_dataHelper->getEmailHelper();
        if ($emailHelper->isEmailEnable(Email::EMAIL_TYPE_CREDIT)) {
            $credit = $this->getCredit();

            if ($credit != null) {

                $notification = $credit->getCreditNotification() === null
                    ? true : (boolean)$credit->getCreditNotification();
                if (!$notification) {
                    return $this;
                }

                $customer = $this->customerFactory->create()->load($credit->getCustomerId());
                if (!$customer || !$customer->getId()) {
                    return $this;
                }

                $emailHelper->sendEmailTemplate(
                    Email::EMAIL_TYPE_CREDIT,
                    $customer->getName(),
                    $customer->getEmail(),
                    [
                        'customer' => $customer,
                        'title' => Action::getActionLabel($this->getAction(), $this->getExtraContent()),
                        'credit_amount' => $this->_dataHelper->convertPrice($this->getAmount(), true, false),
                        'customer_balance' => $this->_dataHelper->convertPrice($credit->getBalance(), true, false)
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * @param $customer
     * @throws LocalizedException
     */
    public function updateExpired($customer)
    {
        if (!$customer || !$customer->getId()) {
            return;
        }
        $currentDate = date("Y-m-d h:i:s");
        $collection = $this->getCollection();
        $collection->getSelect()
            ->join(
                ['cr' => $collection->getTable('mageplaza_giftcard_credit')],
                'main_table.expired_at < "'.$currentDate.' + INTERVAL 1 DAY" AND main_table.current_amount > 0.0001 AND main_table.action = 2 AND main_table.credit_id = cr.credit_id AND cr.customer_id = ' . $customer->getId(),
                ['customer_id']
            )->order('expired_at');
            foreach ($collection as $redeemed) {
                if ($redeemed->getStatus() != 5) {
                    $this->createTransaction(
                        Action::ACTION_EXPIRE,
                        -$redeemed->getCurrentAmount(),
                        $customer->getId(),
                        $redeemed->getExpiredAt(),
                        ['expired' => $this->_dataHelper->formatDate(
                            $redeemed->getExpiredAt(),
                            IntlDateFormatter::MEDIUM
                        )],
                        5
                    );
                }
                $customer->setBalance($customer->getBalance() - $redeemed->getCurrentAmount());
                $customer->save();
                $redeemed->setCurrentAmount(0.000);
                $redeemed->setStatus(5);
            }
            $collection->save();
        }

        public function updateOldData(){
            /** update old data before day 19-11-21 */
            $creditFactory = $this->creditFactory->create()
                ->getCollection();
            foreach ($creditFactory as $customer) {
                $customerId = $customer->getCustomerId();
                $customerBalance = $customer->getBalance();
                $currentDate = date("Y-m-d h:i:s");
                $oldTransactions = $this->getCollection()->setOrder('transaction_id', 'desc');
                $oldTransactions->getSelect()
                    ->join(
                        ['cr' => $oldTransactions->getTable('mageplaza_giftcard_credit')],
                        'main_table.created_at < "'.$currentDate.' + INTERVAL 1 DAY" AND main_table.credit_id = cr.credit_id AND main_table.action = 2 AND cr.customer_id = ' . $customerId,
                        ['customer_id']
                    );

                    foreach ($oldTransactions as $value) {
                        if($value->getStatus() != 5) {
                            if (!is_array($value->getExtraContent())) {
                                $extraContent = DataHelper::jsonDecode($value->getExtraContent());
                            }
                            if (isset($extraContent['code'])) {
                                $code = $extraContent['code'];
                                /** @var Collection $histories */
                                $histories = $this->_historyFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('code', $code)
                                    ->addFieldToFilter('status', 4)
                                    ->setOrder('created_at', 'desc')
                                    ->getFirstItem();
                                if ($histories->getAmount()) {
                                    $value->setAmount(abs($histories->getAmount()));
                                }
                                if ($customerBalance > 0.0001) {
                                    if ($customerBalance > $value->getAmount()) {
                                        $value->setCurrentAmount(abs($value->getAmount()));
                                        $customerBalance = $customerBalance - abs($value->getAmount());
                                    } else {
                                        $value->setCurrentAmount(abs($customerBalance));
                                        $customerBalance = 0;
                                    }
                                } else {
                                    $value->setCurrentAmount(0.000);
                                    $value->setStatus(5);
                                }
                            }
                        }
                    }
                $oldTransactions->save();
            }
        }
}