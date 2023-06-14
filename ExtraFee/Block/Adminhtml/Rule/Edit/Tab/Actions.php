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
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Tax\Model\TaxClass\Source\Product as TaxProduct;
use Mageplaza\ExtraFee\Model\Config\Source\ApplyType;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayArea;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayType;
use Mageplaza\ExtraFee\Model\Config\Source\FeeType;
use Mageplaza\ExtraFee\Model\Rule;

/**
 * Class Actions
 * @package Mageplaza\ExtraFee\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * @var TaxProduct
     */
    protected $taxProduct;

    /**
     * @var ApplyType
     */
    protected $applyType;

    /**
     * @var DisplayType
     */
    protected $displayType;

    /**
     * @var DisplayArea
     */
    protected $displayArea;

    /**
     * @var FeeType
     */
    protected $feeType;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesno
     * @param TaxProduct $taxProduct
     * @param ApplyType $applyType
     * @param DisplayType $displayType
     * @param DisplayArea $displayArea
     * @param FeeType $feeType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesno,
        TaxProduct $taxProduct,
        ApplyType $applyType,
        DisplayType $displayType,
        DisplayArea $displayArea,
        FeeType $feeType,
        array $data = []
    ) {
        $this->yesno       = $yesno;
        $this->taxProduct  = $taxProduct;
        $this->applyType   = $applyType;
        $this->displayType = $displayType;
        $this->displayArea = $displayArea;
        $this->feeType     = $feeType;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Rule $rule */
        $rule = $this->_coreRegistry->registry('mageplaza_extrafee_rule');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $actionsFieldset = $form->addFieldset('actions_fieldset', [
            'legend' => __('Actions'),
            'class'  => 'fieldset-wide'
        ]);
        $applyType       = $actionsFieldset->addField('apply_type', 'select', [
            'name'   => 'apply_type',
            'label'  => __('Apply Type'),
            'title'  => __('Apply Type'),
            'values' => $this->applyType->toOptionArray()
        ]);
        $feeType         = $actionsFieldset->addField('fee_type', 'select', [
            'name'   => 'fee_type',
            'label'  => __('Fee Type'),
            'title'  => __('Fee Type'),
            'values' => $this->feeType->toOptionArray()
        ]);
        $amount          = $actionsFieldset->addField('amount', 'text', [
            'name'     => 'amount',
            'label'    => __('Fee Amount'),
            'title'    => __('Fee Amount'),
            'class'    => 'validate-not-negative-number',
            'required' => true
        ]);
        $displayArea     = $actionsFieldset->addField('area', 'select', [
            'name'   => 'area',
            'label'  => __('Display Area'),
            'title'  => __('Display Area'),
            'values' => $this->displayArea->toOptionArray()
        ]);
        $displayType     = $actionsFieldset->addField('display_type', 'select', [
            'name'   => 'display_type',
            'label'  => __('Display Type'),
            'title'  => __('Display Type'),
            'values' => $this->displayType->toOptionArray()

        ]);
        $isRequired      = $actionsFieldset->addField('is_required', 'select', [
            'name'   => 'is_required',
            'label'  => __('Is Required'),
            'title'  => __('Is Required'),
            'values' => $this->yesno->toOptionArray()
        ]);
        $actionsFieldset->addField('fee_tax', 'select', [
            'name'   => 'fee_tax',
            'label'  => __('Fee Tax'),
            'title'  => __('Fee Tax'),
            'values' => $this->taxProduct->toOptionArray()
        ]);
        $actionsFieldset->addField('sort_order', 'text', [
            'name'  => 'sort_order',
            'label' => __('Cart Sort Order'),
            'title' => __('Cart Sort Order'),
        ]);
        $actionsFieldset->addField('refundable', 'select', [
            'name'   => 'refundable',
            'label'  => __('Refundable'),
            'title'  => __('Refundable'),
            'values' => $this->yesno->toOptionArray()
        ]);
        $actionsFieldset->addField('stop_further_processing', 'select', [
            'name'   => 'stop_further_processing',
            'label'  => __('Stop further processing'),
            'title'  => __('Stop further processing'),
            'values' => $this->yesno->toOptionArray()
        ]);

        $this->setChild('form_after', $this->getLayout()->createBlock(Dependence::class)
            ->addFieldMap($applyType->getHtmlId(), $applyType->getName())
            ->addFieldMap($feeType->getHtmlId(), $feeType->getName())
            ->addFieldMap($amount->getHtmlId(), $amount->getName())
            ->addFieldMap($displayArea->getHtmlId(), $displayArea->getName())
            ->addFieldMap($displayType->getHtmlId(), $displayType->getName())
            ->addFieldMap($isRequired->getHtmlId(), $isRequired->getName())
            ->addFieldDependence($feeType->getName(), $applyType->getName(), ApplyType::AUTOMATIC)
            ->addFieldDependence($amount->getName(), $applyType->getName(), ApplyType::AUTOMATIC)
            ->addFieldDependence($displayArea->getName(), $applyType->getName(), ApplyType::MANUAL)
            ->addFieldDependence($displayType->getName(), $applyType->getName(), ApplyType::MANUAL)
            ->addFieldDependence($isRequired->getName(), $applyType->getName(), ApplyType::MANUAL));

        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
