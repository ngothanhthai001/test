<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Sales Rules Wizard for Magento 2 (System)
 */

namespace Amasty\SalesRuleWizard\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions as BlockActions;
use Magento\SalesRule\Model\RuleFactory;

class Actions extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Actions implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var string
     */
    protected $_nameInLayout = 'actions_apply_to';

    /**
     * @var RuleFactory|mixed
     */
    private $ruleFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $sourceYesno,
        BlockActions $ruleActions,
        Fieldset $rendererFieldset,
        array $data = [],
        RuleFactory $ruleFactory = null
    ) {
        $this->ruleFactory = $ruleFactory ?: ObjectManager::getInstance()->get(RuleFactory::class);
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $sourceYesno,
            $ruleActions,
            $rendererFieldset,
            $data,
            $ruleFactory
        );
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->ruleFactory->create();
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return \Magento\Backend\Block\Widget\Form\Generic::_prepareForm();
    }

    /**
     * Handles addition of actions tab to supplied form.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm(
        $model,
        $fieldsetId = 'actions_fieldset',
        $formName = 'amasty_promowizard_rule_settings'
    ) {
        $actionsFieldSetId = $model->getActionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'amasty_promowizard/wizard/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset
            ->setTemplate('Amasty_SalesRuleWizard::promo/fieldset.phtml')
            ->setNameInLayout('promo.wizard.fieldset')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($actionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            [
                'name' => 'apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        return $form;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $actions
     * @param string $formName
     * @return void
     */
    private function setActionFormName(\Magento\Rule\Model\Condition\AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
    }
}
