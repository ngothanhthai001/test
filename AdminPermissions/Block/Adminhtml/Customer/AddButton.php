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

namespace Mageplaza\AdminPermissions\Block\Adminhtml\Customer;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class AddButton
 * @package Mageplaza\AdminPermission\Block\Adminhtml\Customer\Edit
 */
class AddButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * AddButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->helperData->isEnabled()
            && !$this->helperData->isAllow('Mageplaza_AdminPermissions::customer_create')
        ) {
            return [];
        }

        return [
            'label' => __('Add New Customer'),
            'class' => 'primary',
            'url'   => $this->getUrl('*/*/new')
        ];
    }
}
