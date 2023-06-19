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

namespace Mageplaza\AdminPermissions\Observer;

use Exception;
use Magento\Authorization\Model\Role;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions;
use Psr\Log\LoggerInterface;

/**
 * Class RoleSaveAfter
 * @package Mageplaza\AdminPermissions\Observer
 */
class RoleSaveAfter implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AdminPermissions
     */
    protected $apResource;

    /**
     * @var MessageManagerInterface
     */

    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RoleSaveAfter constructor.
     *
     * @param RequestInterface $request
     * @param MessageManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param AdminPermissions $apResource
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        MessageManagerInterface $messageManager,
        LoggerInterface $logger,
        AdminPermissions $apResource,
        Data $helperData
    ) {
        $this->request        = $request;
        $this->apResource     = $apResource;
        $this->helperData     = $helperData;
        $this->messageManager = $messageManager;
        $this->logger         = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Role $role */
        $role = $observer->getEvent()->getObject();
        if (!$this->helperData->isEnabled() || $this->request->getFullActionName() !== 'adminhtml_user_role_saverole') {
            return;
        }
        $adminPermissions = $this->helperData->getAdminPermission($role->getId());
        $data             = $this->prepareData();
        $adminPermissions->addData($data);
        $adminPermissions->setRoleId($role->getId());
        try {
            $this->apResource->save($adminPermissions);
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Something went wrong while saving admin permissions'));
        }
    }

    /**
     * @return array
     */
    protected function prepareData()
    {
        $data   = $this->request->getParams();
        $result = [];
        foreach ($data as $key => $datum) {
            if ($key === 'role_id' || strncmp($key, 'mp_', 3) === 0) {
                if ($key === 'mp_custom_limit') {
                    $result[$key] = Data::jsonEncode($datum);
                } elseif (is_array($datum)) {
                    $result[$key] = implode(',', $datum);
                } elseif (strpos($key, 'ids') !== false) {
                    $result[$key] = str_replace('&', ',', $datum);
                } else {
                    $result[$key] = $datum;
                }
            }
        }

        return $result;
    }
}
