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

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Mageplaza\AdminPermissions\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractLimitAccess
 * @package Mageplaza\AdminPermissions\Observer
 */
abstract class AbstractLimitAccess implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * AbstractLimitAccess constructor.
     *
     * @param LoggerInterface $logger
     * @param Data $helperData
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->logger     = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return ResponseInterface|void
     */
    public function execute(Observer $observer)
    {
        $adminPermissions = $this->helperData->getAdminPermission();
        if (!$this->helperData->isPermissionEnabled()) {
            return;
        }
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        /** @var View $controller */
        $controller = $observer->getEvent()->getControllerAction();

        try {
            $object = $this->getObject($request);
        } catch (Exception $e) {
            $this->logger->critical($e);

            return;
        }

        $allowRestrictionStoreIds = $this->helperData->getAllowedRestrictionStoreIds($adminPermissions);
        $storeId                  = (string) $object->getStoreId();

        if (!empty($allowRestrictionStoreIds) && !in_array($storeId, $allowRestrictionStoreIds, true)) {
            $this->helperData->forwardToDeniedPage($controller, $request);
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return mixed
     */
    abstract protected function getObject($request);
}
