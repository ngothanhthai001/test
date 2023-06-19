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
use Magento\Backend\Block\Widget\Grid\Massaction;
use Magento\Backend\Helper\Data;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Action\Add;
use Mageplaza\AdminPermissions\Helper\Data as HelperData;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;
use Mageplaza\AdminPermissions\Model\ResourceModel\Custom\CollectionFactory;

/**
 * Class Custom
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class Custom extends AbstractRenderer
{
    const PARAM_NAME = 'mp_ap_custom_ids';
    const FILTER_COL = 'in_custom';
    const ENTITY_ID  = 'id';
    const GRID_ID    = 'customLimitGrid';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AdminPermissions::custom-limit.phtml';

    /**
     * @var CollectionFactory
     */
    protected $customLimitFactory;

    /**
     * UserRole constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param CollectionFactory $customLimitFactory
     * @param array $data
     */

    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        CollectionFactory $customLimitFactory,
        array $data = []
    ) {
        $this->customLimitFactory = $customLimitFactory;

        parent::__construct($context, $backendHelper, $adminPermissionsFactory, $adminPermissionsResource, $data);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->customLimitFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('mp_custom_id', [
            'header'           => __('ID'),
            'type'             => 'number',
            'index'            => 'id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('type', [
            'header'   => __('Type'),
            'index'    => 'type',
            'type'     => 'options',
            'options'  => [
                'model'      => __('Model'),
                'controller' => __('Controller')
            ],
            'sortable' => true
        ]);

        $this->addColumn('class', [
            'header'   => __('Class'),
            'index'    => 'class',
            'type'     => 'text',
            'sortable' => true
        ]);

        $this->addColumn('action', [
            'header'   => __('Action'),
            'sortable' => false,
            'filter'   => false,
            'renderer' => Add::class,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return $this|AbstractRenderer
     */
    protected function _prepareMassaction()
    {
        $this->addExportType(
            $this->getUrl(
                'mpadminpermissions/grid/exportcustomlimitcsv',
                ['rid' => $this->getAdminPermissions()->getRoleId()]
            ),
            'CSV'
        );

        $this->setMassactionIdField('id');
        /** @var Massaction $massAction */
        $massAction = $this->getMassactionBlock();
        $massAction->setFormFieldName('ids');
        $massAction->setUseAjax(true);
        $massAction->setHideFormElement(true);
        $massAction->addItem('add', [
            'label' => __('Add'),
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadminpermissions/grid/customlimit');
    }

    /**
     * @return array|mixed
     */
    public function getCustomData()
    {
        $adminPermission = $this->getAdminPermissions();
        $data            = $adminPermission->getMpCustomLimit();

        return $data ? HelperData::jsonDecode($data) : [];
    }
}
