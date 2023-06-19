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

namespace Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Framework\DataObject;

/**
 * Class CustomerName
 * @package Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer
 */
class CustomerName extends AbstractRenderer
{
    /**
     * @var View
     */
    private $customerHelper;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * CustomerName constructor.
     *
     * @param Context $context
     * @param View $customerHelper
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        View $customerHelper,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->customerHelper  = $customerHelper;
        $this->customerFactory = $customerFactory;

        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param DataObject $row
     *
     * @return  string
     */
    public function render(DataObject $row)
    {
        $customer = $this->customerFactory->create(['data' => $row->getData()]);

        return $this->customerHelper->getCustomerName($customer);
    }
}
