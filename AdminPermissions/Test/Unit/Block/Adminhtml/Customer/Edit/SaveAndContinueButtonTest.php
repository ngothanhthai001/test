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

namespace Mageplaza\AdminPermissions\Test\Unit\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Mageplaza\AdminPermissions\Block\Adminhtml\Customer\Edit\SaveAndContinueButton;
use Mageplaza\AdminPermissions\Helper\Data;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class SaveAndContinueButtonTest
 * @package Mageplaza\AdminPermissions\Test\Unit\Block\Adminhtml\Customer\Edit
 */
class SaveAndContinueButtonTest extends TestCase
{
    /**
     * @var SaveAndContinueButton
     */
    protected $object;

    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Registry|PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var AccountManagementInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAccountManagement;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $helperData;

    /**
     * Test Get Template Base Specification
     */
    public function testGetButtonData()
    {
        $customerId = 1;

        $this->registry->method('registry')->with(RegistryConstants::CURRENT_CUSTOMER_ID)->willReturn($customerId);

        $this->customerAccountManagement->method('isReadOnly')->with($customerId)->willReturn(false);
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('isAllow')->with('Mageplaza_AdminPermissions::customer_edit')->willReturn(true);

        $expectResult = [
            'label'          => __('Save and Continue Edit'),
            'class'          => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order'     => 80
        ];
        $actualResult = $this->object->getButtonData();
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->context                   = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->registry                  = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $this->customerAccountManagement = $this->getMockBuilder(AccountManagementInterface::class)->getMock();
        $this->helperData                = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        $this->object = new SaveAndContinueButton(
            $this->context,
            $this->registry,
            $this->customerAccountManagement,
            $this->helperData
        );
    }
}
