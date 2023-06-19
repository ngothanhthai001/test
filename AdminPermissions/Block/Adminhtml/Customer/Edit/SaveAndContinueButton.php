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

namespace Mageplaza\AdminPermissions\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Registry;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class SaveAndContinueButton
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Customer\Edit
 */
class SaveAndContinueButton extends \Magento\Customer\Block\Adminhtml\Edit\SaveAndContinueButton
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * SaveAndContinueButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AccountManagementInterface $customerAccountManagement
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        Data $helperData
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $registry, $customerAccountManagement);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->getCustomerId()
            && $this->helperData->isEnabled()
            && !$this->helperData->isAllow('Mageplaza_AdminPermissions::customer_edit')
        ) {
            return [];
        }

        return parent::getButtonData();
    }
}
