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
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Mageplaza\AdminPermissions\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class LimitAccessCreditMemo
 * @package Mageplaza\AdminPermissions\Observer
 */
class LimitAccessCreditMemo extends AbstractLimitAccess
{
    /**
     * @var CreditmemoRepository
     */
    private $creditmemoRepository;

    /**
     * LimitAccessCreditMemo constructor.
     *
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param CreditmemoRepository $creditmemoRepository
     */

    public function __construct(
        LoggerInterface $logger,
        Data $helperData,
        CreditmemoRepository $creditmemoRepository
    ) {
        $this->creditmemoRepository = $creditmemoRepository;

        parent::__construct($logger, $helperData);
    }

    /**
     * @param RequestInterface $request
     *
     * @return CreditmemoInterface|mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getObject($request)
    {
        $creditmemoId = $request->getParam('creditmemo_id');

        return $this->creditmemoRepository->get($creditmemoId);
    }
}
