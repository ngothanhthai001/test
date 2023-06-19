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
 * @package     Mageplaza_AdminPermissions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Observer\Sales;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Magento\Sales\Model\OrderRepository;
use Mageplaza\AdminPermissions\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class LimitAccessTransaction
 * @package Mageplaza\AdminPermissions\Observer
 */
class LimitAccessTransaction extends AbstractLimitAccess
{
    /**
     * @var Repository
     */
    private $transactionRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * LimitAccessTransaction constructor.
     *
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param Repository $transactionRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helperData,
        Repository $transactionRepository,
        OrderRepository $orderRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository       = $orderRepository;

        parent::__construct($logger, $helperData);
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|AbstractModel|TransactionInterface|mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getObject($request)
    {
        $transactionId = $request->getParam('txn_id');
        $orderId       = $this->transactionRepository->get($transactionId)->getOrderId();

        return $this->orderRepository->get($orderId);
    }
}
