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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Mageplaza\AdminPermissions\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class LimitAccessOrder
 * @package Mageplaza\AdminPermissions\Observer
 */
class LimitAccessOrder extends AbstractLimitAccess
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * LimitAccessOrder constructor.
     *
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helperData,
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;

        parent::__construct($logger, $helperData);
    }

    /**
     * @param RequestInterface $request
     *
     * @return OrderInterface|mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getObject($request)
    {
        $orderId = $request->getParam('order_id');

        return $this->orderRepository->get($orderId);
    }
}
