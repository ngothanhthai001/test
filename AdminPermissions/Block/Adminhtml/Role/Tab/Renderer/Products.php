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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer\MassActionCheckBox;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class Products
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class Products extends AbstractRenderer
{
    const PARAM_NAME = 'mp_product_ids';
    const FILTER_COL = 'in_products';
    const ENTITY_ID  = 'entity_id';
    const GRID_ID    = 'productsGrid';

    /**
     * @var CollectionFactory
     */
    protected $_productColFactory;

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param CollectionFactory $productColFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        CollectionFactory $productColFactory,
        array $data = []
    ) {
        $this->_productColFactory = $productColFactory;

        parent::__construct($context, $backendHelper, $adminPermissionsFactory, $adminPermissionsResource, $data);
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productColFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price');
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
            'name'             => 'in_product',
            'values'           => $this->_getSelectedIds(),
            'align'            => 'center',
            'renderer'         => MassActionCheckBox::class,
            'index'            => 'entity_id',
        ]);

        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name',
            'width'  => '50px',
        ]);
        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index'  => 'sku',
            'width'  => '50px',
        ]);
        $this->addColumn('price', [
            'header' => __('Price'),
            'type'   => 'currency',
            'index'  => 'price',
            'width'  => '50px',
        ]);
        $this->addColumn('position', [
            'header'           => __('Position'),
            'name'             => 'position',
            'header_css_class' => 'hidden',
            'column_css_class' => 'hidden',
            'validate_class'   => 'validate-number',
            'index'            => 'position',
            'editable'         => true
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadminpermissions/grid/products');
    }
}
