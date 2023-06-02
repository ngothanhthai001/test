<?php

declare(strict_types=1);

namespace Amasty\ShopbyBrand\Block\Adminhtml\Slider\Edit;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element as RenderElement;
use Amasty\ShopbyBrand\Controller\RegistryConstants;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var RenderElement
     */
    protected $_renderer;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RenderElement $renderer,
        array $data = []
    ) {
        $this->_renderer = $renderer;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $attributeCode = $this->getRequest()->getParam(FilterSettingInterface::ATTRIBUTE_CODE);
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        /** @var \Amasty\ShopbyPage\Api\Data\PageInterface $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEATURED);
        $urlParams = [
            'option_id' => (int)$optionId,
            'attribute_code' => $attributeCode,
            'store' => (int)$storeId
        ];
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('*/*/save', $urlParams),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $form->setFieldsetElementRenderer($this->_renderer);
        $form->setDataObject($model);

        $this->_eventManager->dispatch(
            'amshopby_option_form_build_after',
            [
                'form' => $form,
                'setting' => $model,
                'is_slider' => true,
                'store_id' => $storeId
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
