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

namespace Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Category;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Custom as CustomRenderer;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Customers as CustomersRenderer;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\ProductAttributes as ProductAttributesRenderer;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Products as ProductsRenderer;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\UserRole as UserRoleRenderer;
use Mageplaza\AdminPermissions\Model\AdminPermissions as AdminPermissionsModel;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\Config\Source\CategoryRestriction;
use Mageplaza\AdminPermissions\Model\Config\Source\CustomerRestriction;
use Mageplaza\AdminPermissions\Model\Config\Source\Product as ProductConfig;
use Mageplaza\AdminPermissions\Model\Config\Source\ProductAttributeRestriction;
use Mageplaza\AdminPermissions\Model\Config\Source\ProductRestriction;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;
use Mageplaza\AdminPermissions\Model\Config\Source\SalesRestriction;
use Mageplaza\AdminPermissions\Model\Config\Source\UserRoleRestriction;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;

/**
 * Class AdminPermissions
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab
 */
class AdminPermissions extends Generic implements TabInterface
{
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * @var SalesRestriction
     */
    protected $salesRestriction;

    /**
     * @var CategoryConfig
     */
    protected $categoryConfig;

    /**
     * @var ProductConfig
     */
    protected $productConfig;

    /**
     * @var AdminPermissionsModel
     */
    protected $_object;

    /**
     * @var AdminPermissionsFactory
     */
    protected $adminPermissionsFactory;

    /**
     * @var AdminPermissionsResource
     */
    protected $adminPermissionsResource;

    /**
     * @var CategoryRestriction
     */
    private $categoryRestriction;

    /**
     * @var ProductRestriction
     */
    private $productRestriction;

    /**
     * @var CustomerRestriction
     */
    private $customerRestriction;

    /**
     * @var ProductAttributeRestriction
     */
    private $productAttributeRestriction;

    /**
     * @var UserRoleRestriction
     */
    private $userRoleRestriction;

    /**
     * AdminPermissions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FieldFactory $fieldFactory
     * @param Store $systemStore
     * @param Yesno $yesno
     * @param SalesRestriction $salesRestriction
     * @param CategoryRestriction $categoryRestriction
     * @param ProductRestriction $productRestriction
     * @param CustomerRestriction $customerRestriction
     * @param ProductAttributeRestriction $productAttributeRestriction
     * @param UserRoleRestriction $userRoleRestriction
     * @param ProductConfig $productConfig
     * @param AdminPermissionsFactory $adminPermissionsFactory
     * @param AdminPermissionsResource $adminPermissionsResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldFactory $fieldFactory,
        Store $systemStore,
        Yesno $yesno,
        SalesRestriction $salesRestriction,
        CategoryRestriction $categoryRestriction,
        ProductRestriction $productRestriction,
        CustomerRestriction $customerRestriction,
        ProductAttributeRestriction $productAttributeRestriction,
        UserRoleRestriction $userRoleRestriction,
        ProductConfig $productConfig,
        AdminPermissionsFactory $adminPermissionsFactory,
        AdminPermissionsResource $adminPermissionsResource,
        array $data = []
    ) {
        $this->fieldFactory                = $fieldFactory;
        $this->systemStore                 = $systemStore;
        $this->yesno                       = $yesno;
        $this->salesRestriction            = $salesRestriction;
        $this->categoryRestriction         = $categoryRestriction;
        $this->productRestriction          = $productRestriction;
        $this->customerRestriction         = $customerRestriction;
        $this->userRoleRestriction         = $userRoleRestriction;
        $this->productAttributeRestriction = $productAttributeRestriction;
        $this->productConfig               = $productConfig;
        $this->adminPermissionsFactory     = $adminPermissionsFactory;
        $this->adminPermissionsResource    = $adminPermissionsResource;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form          = $this->_formFactory->create();
        $salesFieldset = $form->addFieldset('mp_sales_fieldset', [
            'legend'      => __('Sales'),
            'class'       => 'fieldset-wide',
            'collapsable' => true
        ]);

        $salesRestriction = $salesFieldset->addField('mp_sales_restriction', 'select', [
            'name'   => 'mp_sales_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->salesRestriction->toOptionArray()
        ]);

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(Element::class);
        $storeIds      = $salesFieldset->addField('mp_store_ids', 'multiselect', [
            'name'   => 'mp_store_ids',
            'label'  => __('Store Views'),
            'title'  => __('Store Views'),
            'values' => $this->systemStore->getStoreValuesForForm()
        ])->setRenderer($rendererBlock);

        $categoryFieldset = $form->addFieldset('mp_category_fieldset', [
            'legend'      => __('Category'),
            'class'       => 'fieldset-wide',
            'collapsable' => true
        ]);

        $categoryRestriction = $categoryFieldset->addField('mp_category_restriction', 'select', [
            'name'   => 'mp_category_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->categoryRestriction->toOptionArray()
        ]);

        $categoryIds = $categoryFieldset->addField('mp_category_ids', Category::class, [
            'name'  => 'mp_category_ids',
            'label' => __('Category'),
            'title' => __('Category'),
            'note'  => __('You have to select Root Category before selecting Children Categories  ')
        ]);

        $productFieldset    = $form->addFieldset('mp_product_fieldset', [
            'legend'      => __('Product'),
            'class'       => 'fieldset-wide',
            'collapsable' => true
        ]);
        $productRestriction = $productFieldset->addField('mp_product_restriction', 'select', [
            'name'   => 'mp_product_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->productRestriction->toOptionArray()
        ]);
        $productApplyFor    = $productFieldset->addField('mp_product_apply_for', 'select', [
            'name'   => 'mp_product_apply_for',
            'label'  => __('Apply For'),
            'title'  => __('Apply For'),
            'value'  => ProductConfig::ALL,
            'values' => $this->productConfig->toOptionArray()
        ]);

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(ProductsRenderer::class);
        $productsIds   = $productFieldset->addField('mp_product_ids', 'text', [
            'name'         => 'mp_product_ids',
            'container_id' => 'row_mp_product_ids',
        ])->setRenderer($rendererBlock);

        $customerFieldset    = $form->addFieldset('mp_customer_fieldset', [
            'legend'      => __('Customer'),
            'class'       => 'fieldset-wide',
            'collapsable' => true
        ]);
        $customerRestriction = $customerFieldset->addField('mp_customer_restriction', 'select', [
            'name'   => 'mp_customer_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->customerRestriction->toOptionArray()
        ]);
        $rendererBlock       = $this->getLayout()->createBlock(CustomersRenderer::class);
        $customerIds         = $customerFieldset->addField('mp_customer_ids', 'text', [
            'name'         => 'mp_customer_ids',
            'container_id' => 'row_mp_customer_ids',
        ])->setRenderer($rendererBlock);

        $productAttributeFieldset    = $form->addFieldset('mp_product_attribute_fieldset', [
            'legend'      => __('Product Attributes'),
            'class'       => 'fieldset-wide',
            'collapsable' => true

        ]);
        $productAttributeRestriction = $productAttributeFieldset->addField('mp_prodattr_restriction', 'select', [
            'name'   => 'mp_prodattr_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->productAttributeRestriction->toOptionArray()
        ]);

        $rendererBlock = $this->getLayout()->createBlock(ProductAttributesRenderer::class);
        $prodAttrIds   = $productAttributeFieldset->addField('mp_prodattr_ids', 'text', [
            'name'         => 'mp_prodattr_ids',
            'container_id' => 'row_mp_prodattr_ids',
        ])->setRenderer($rendererBlock);

        $userRoleFieldset    = $form->addFieldset('mp_user_role_fieldset', [
            'legend'      => __('User Role'),
            'class'       => 'fieldset-wide',
            'collapsable' => true

        ]);
        $userRoleRestriction = $userRoleFieldset->addField('mp_user_role_restriction', 'select', [
            'name'   => 'mp_user_role_restriction',
            'label'  => __('Enable Restriction'),
            'title'  => __('Enable Restriction'),
            'values' => $this->userRoleRestriction->toOptionArray()
        ]);

        $rendererBlock = $this->getLayout()->createBlock(UserRoleRenderer::class);
        $roleIds       = $userRoleFieldset->addField('mp_user_role_ids', 'text', [
            'name'         => 'mp_user_role_ids',
            'container_id' => 'row_mp_user_role_ids',
        ])->setRenderer($rendererBlock);

        $customFieldset = $form->addFieldset('mp_custom_fieldset', [
            'legend'      => __('Customize Limit Action'),
            'class'       => 'fieldset-wide',
            'collapsable' => true
        ]);

        $enableCustom = $customFieldset->addField('mp_custom_enabled', 'select', [
            'name'   => 'mp_custom_enabled',
            'label'  => __('Enable'),
            'title'  => __('Enable'),
            'values' => $this->yesno->toOptionArray()
        ]);

        $rendererBlock = $this->getLayout()->createBlock(CustomRenderer::class);
        $customField   = $customFieldset->addField('mp_custom_limit_ids', 'text', [
            'name'         => 'mp_custom_limit_ids',
            'container_id' => 'row_mp_custom_limit_ids',
        ])->setRenderer($rendererBlock);

        $refField = $this->fieldFactory->create([
            'fieldData'   => ['value' => Restriction::DENY . ',' . Restriction::ALLOW, 'separator' => ','],
            'fieldPrefix' => ''
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($productRestriction->getHtmlId(), $productRestriction->getName())
                ->addFieldMap($productApplyFor->getHtmlId(), $productApplyFor->getName())
                ->addFieldMap($productsIds->getHtmlId(), $productsIds->getName())
                ->addFieldMap($categoryRestriction->getHtmlId(), $categoryRestriction->getName())
                ->addFieldMap($categoryIds->getHtmlId(), $categoryIds->getName())
                ->addFieldMap($customerRestriction->getHtmlId(), $customerRestriction->getName())
                ->addFieldMap($customerIds->getHtmlId(), $customerIds->getName())
                ->addFieldMap($prodAttrIds->getHtmlId(), $prodAttrIds->getName())
                ->addFieldMap($userRoleRestriction->getHtmlId(), $userRoleRestriction->getName())
                ->addFieldMap($roleIds->getHtmlId(), $roleIds->getName())
                ->addFieldMap($salesRestriction->getHtmlId(), $salesRestriction->getName())
                ->addFieldMap($productAttributeRestriction->getHtmlId(), $productAttributeRestriction->getName())
                ->addFieldMap($storeIds->getHtmlId(), $storeIds->getName())
                ->addFieldMap($enableCustom->getHtmlId(), $enableCustom->getName())
                ->addFieldMap($customField->getHtmlId(), $customField->getName())
                ->addFieldDependence($productsIds->getName(), $productApplyFor->getName(), ProductConfig::SPECIFIC)
                ->addFieldDependence($productsIds->getName(), $productRestriction->getName(), $refField)
                ->addFieldDependence($storeIds->getName(), $salesRestriction->getName(), $refField)
                ->addFieldDependence($categoryIds->getName(), $categoryRestriction->getName(), $refField)
                ->addFieldDependence($productApplyFor->getName(), $productRestriction->getName(), $refField)
                ->addFieldDependence($customerIds->getName(), $customerRestriction->getName(), $refField)
                ->addFieldDependence($prodAttrIds->getName(), $productAttributeRestriction->getName(), $refField)
                ->addFieldDependence($roleIds->getName(), $userRoleRestriction->getName(), $refField)
                ->addFieldDependence($customField->getName(), $enableCustom->getName(), '1')
        );

        $this->setForm($form);

        return parent::_prepareForm(); // TODO: Change the autogenerated stub
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Admin Permissions');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return $this->_authorization->isAllowed('Mageplaza_AdminPermissions::admin_permissions');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getObject()->getData());

        return parent::_initFormValues();
    }

    /**
     * @return AdminPermissionsModel
     */
    protected function getObject()
    {
        if ($this->_object === null) {
            $this->_object = $this->adminPermissionsFactory->create();
            $this->adminPermissionsResource->load($this->_object, $this->_request->getParam('rid'), 'role_id');
        }

        return $this->_object;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function toHtml()
    {
        $enableHtml = $this->getLayout()->createBlock(Template::class)
            ->setTemplate('Mageplaza_AdminPermissions::admin-permissions.phtml')->toHtml();

        return $enableHtml . parent::toHtml(); // TODO: Change the autogenerated stub
    }
}
