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

namespace Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Store\Model\System\Store;
use Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer\CustomerName;
use Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer\MassActionCheckBox;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\Config\Source\CustomerGroup;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class Customers
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class Customers extends AbstractRenderer
{
    const PARAM_NAME = 'mp_customer_ids';
    const FILTER_COL = 'in_customers';
    const ENTITY_ID  = 'entity_id';
    const GRID_ID    = 'customersGrid';

    /**
     * @var CollectionFactory
     */
    protected $_customerColFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * Customers constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param Store $systemStore
     * @param CollectionFactory $customerColFactory
     * @param CustomerGroup $customerGroup
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        Store $systemStore,
        CollectionFactory $customerColFactory,
        CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->systemStore         = $systemStore;
        $this->customerGroup       = $customerGroup;
        $this->_customerColFactory = $customerColFactory;

        parent::__construct($context, $backendHelper, $adminPermissionsFactory, $adminPermissionsResource, $data);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_customerColFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(self::FILTER_COL, [
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_customer',
            'values'           => $this->_getSelectedIds(),
            'align'            => 'center',
            'index'            => 'entity_id',
            'renderer'         => MassActionCheckBox::class,
        ]);

        $this->addColumn('entity_id', [
            'header'           => __('Customer ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('firstname', [
            'header'   => __('Name'),
            'index'    => 'firstname',
            'renderer' => CustomerName::class,
            'type'     => 'text',
            'sortable' => true
        ]);

        $this->addColumn('email', [
            'header'   => __('Email'),
            'index'    => 'email',
            'type'     => 'text',
            'sortable' => true
        ]);

        $this->addColumn('group_id', [
            'header'   => __('Customer Group'),
            'index'    => 'group_id',
            'type'     => 'options',
            'sortable' => true,
            'options'  => $this->customerGroup->toArray(),
        ]);

        $this->addColumn('website_id', [
            'header'  => __('Web Site'),
            'index'   => 'website_id',
            'type'    => 'options',
            'options' => $this->systemStore->getWebsiteOptionHash(),
        ]);

        $this->addColumn('store_id', [
            'header'  => __('Account Create In'),
            'index'   => 'store_id',
            'type'    => 'options',
            'options' => $this->systemStore->getStoreOptionHash(),
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadminpermissions/grid/customers');
    }
}
