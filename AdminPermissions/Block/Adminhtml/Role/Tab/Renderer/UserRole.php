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
use Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Store\Model\System\Store;
use Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer\MassActionCheckBox;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class UserRole
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class UserRole extends AbstractRenderer
{
    const PARAM_NAME = 'mp_user_role_ids';
    const FILTER_COL = 'in_roles';
    const ENTITY_ID  = 'role_id';
    const GRID_ID    = 'userRoleGrid';

    /**
     * @var CollectionFactory
     */
    protected $userRoleColFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * UserRole constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param Store $systemStore
     * @param CollectionFactory $customerColFactory
     * @param array $data
     */

    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        Store $systemStore,
        CollectionFactory $customerColFactory,
        array $data = []
    ) {
        $this->systemStore        = $systemStore;
        $this->userRoleColFactory = $customerColFactory;

        parent::__construct($context, $backendHelper, $adminPermissionsFactory, $adminPermissionsResource, $data);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->userRoleColFactory->create();
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
            'name'             => 'in_role',
            'values'           => $this->_getSelectedIds(),
            'align'            => 'center',
            'index'            => 'role_id',
            'renderer'         => MassActionCheckBox::class,
        ]);

        $this->addColumn('mp_role_id', [
            'header'           => __('ID'),
            'type'             => 'number',
            'index'            => 'role_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('mp_role_name', [
            'header'   => __('Role'),
            'index'    => 'role_name',
            'type'     => 'text',
            'sortable' => true
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadminpermissions/grid/userrole');
    }
}
