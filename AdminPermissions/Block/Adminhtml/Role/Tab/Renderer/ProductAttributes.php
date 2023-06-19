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
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer\MassActionCheckBox;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class ProductAttributes
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class ProductAttributes extends AbstractRenderer
{
    const PARAM_NAME = 'mp_prodattr_ids';
    const FILTER_COL = 'in_product_attrs';
    const ENTITY_ID  = 'main_table.attribute_id';
    const GRID_ID    = 'productAttrGrid';

    /**
     * @var CollectionFactory
     */
    protected $_productAttrColFactory;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * ProductAttributes constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param Yesno $yesno
     * @param CollectionFactory $productAttrColFactory
     * @param array $data
     */

    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        Yesno $yesno,
        CollectionFactory $productAttrColFactory,
        array $data = []
    ) {
        $this->yesno                  = $yesno;
        $this->_productAttrColFactory = $productAttrColFactory;

        parent::__construct($context, $backendHelper, $adminPermissionsFactory, $adminPermissionsResource, $data);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productAttrColFactory->create();
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
            'name'             => 'in_product_attr',
            'values'           => $this->_getSelectedIds(),
            'align'            => 'center',
            'index'            => 'attribute_id',
            'renderer'         => MassActionCheckBox::class,
        ]);

        $this->addColumn('mp_attribute_code', [
            'header'           => __('Attribute Code'),
            'type'             => 'text',
            'index'            => 'attribute_code',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('frontend_label', [
            'header' => __('Default Label'),
            'index'  => 'frontend_label',
        ]);
        $this->addColumn('is_required', [
            'header'  => __('Required'),
            'index'   => 'is_required',
            'type'    => 'options',
            'options' => $this->yesno->toArray(),
        ]);
        $this->addColumn('is_user_defined', [
            'header'           => __('System'),
            'index'            => 'is_user_defined',
            'type'             => 'options',
            'options'          => [
                '0' => __('Yes'), // intended reverted use
                '1' => __('No'), // intended reverted use
            ],
            'header_css_class' => 'col-system',
            'column_css_class' => 'col-system'
        ]);
        $this->addColumn('is_global', [
            'header'  => __('Scope'),
            'index'   => 'is_global',
            'type'    => 'options',
            'options' => [
                ScopedAttributeInterface::SCOPE_STORE   => __('Store View'),
                ScopedAttributeInterface::SCOPE_WEBSITE => __('Web Site'),
                ScopedAttributeInterface::SCOPE_GLOBAL  => __('Global'),
            ],
            'align'   => 'center'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadminpermissions/grid/productattributes');
    }
}
