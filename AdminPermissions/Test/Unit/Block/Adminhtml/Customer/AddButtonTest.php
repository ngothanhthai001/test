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

namespace Mageplaza\AdminPermissions\Test\Unit\Block\Adminhtml\Customer;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mageplaza\AdminPermissions\Block\Adminhtml\Customer\AddButton;
use Mageplaza\AdminPermissions\Helper\Data;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AddButtonTest
 * @package Mageplaza\AdminPermissions\Test\Unit\Block\Adminhtml\Customer
 */
class AddButtonTest extends TestCase
{
    /**
     * @var AddButton
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
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $helperData;

    /**
     * @var UrlInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * Test Get Template Base Specification
     */
    public function testGetButtonData()
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('isAllow')->with('Mageplaza_AdminPermissions::customer_create')->willReturn(true);

        $this->urlBuilder->method('getUrl')->with('*/*/new')->willReturn('addUrl');
        $expectResult = [
            'label' => __('Add New Customer'),
            'class' => 'primary',
            'url'   => 'addUrl'
        ];
        $actualResult = $this->object->getButtonData();
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->context    = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->registry   = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $this->helperData = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)->getMock();
        $this->context->method('getUrlBuilder')->willReturn($this->urlBuilder);

        $this->object = new AddButton(
            $this->context,
            $this->registry,
            $this->helperData
        );
    }
}
