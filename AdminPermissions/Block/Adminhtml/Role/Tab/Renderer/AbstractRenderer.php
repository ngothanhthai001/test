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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Model\AdminPermissions;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class AbstractRenderer
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class AbstractRenderer extends Extended implements RendererInterface
{
    const PARAM_NAME = '';
    const FILTER_COL = '';
    const ENTITY_ID  = '';
    const GRID_ID    = '';

    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var AdminPermissions
     */
    protected $adminPermission;

    /**
     * @var AdminPermissionsFactory
     */
    protected $adminPermissionsFactory;

    /**
     * @var AdminPermissionsResource
     */
    protected $adminPermissionsResource;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AdminPermissions::grid.phtml';

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        array $data = []
    ) {
        $this->adminPermissionsFactory  = $adminPermissionsFactory;
        $this->adminPermissionsResource = $adminPermissionsResource;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId($this::GRID_ID);
        $this->setDefaultSort($this::ENTITY_ID);
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        if ($this->getAdminPermissions()->getId() && $this->getAdminPermissions()->getData($this::PARAM_NAME)) {
            $this->setDefaultFilter([$this::FILTER_COL => 1]);
        }
    }

    /**
     * Retrieve selected Tags
     * @return array
     */
    protected function _getSelectedIds()
    {
        $ids = $this->getRequest()->getPost($this::PARAM_NAME);
        if (!is_array($ids)) {
            $ids = $this->getAdminPermissions()->getData($this::PARAM_NAME);

            return array_filter(explode(',', $ids));
        }

        return array_filter($ids);
    }

    /**
     * @return AdminPermissions
     */
    public function getAdminPermissions()
    {
        if (!$this->adminPermission) {
            $roleId                = $this->getRequest()->getParam('rid');
            $this->adminPermission = $this->adminPermissionsFactory->create();
            $this->adminPermissionsResource->load($this->adminPermission, $roleId, 'role_id');
        }

        return $this->adminPermission;
    }

    /**
     * Retrieve selected Tags
     * @return array
     */
    public function getSelectedRows()
    {
        $adminPermission = $this->getAdminPermissions();
        $selected        = [];
        if ($adminPermission) {
            $selected = explode(',', $adminPermission->getData($this::PARAM_NAME));
        }

        return $selected;
    }

    /**
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() === $this::FILTER_COL) {
            $ids = $this->_getSelectedIds();
            if (empty($ids)) {
                $ids = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter($this::ENTITY_ID, ['in' => $ids]);
            } elseif ($ids) {
                $this->getCollection()->addFieldToFilter($this::ENTITY_ID, ['nin' => $ids]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @param object $row
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->toHtml();
    }
}
