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

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Ui\Component\Product\Form\Categories\Options;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;

/**
 * Class Category
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer
 */
class Category extends Multiselect
{
    /**
     * @var CategoryCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var Options
     */
    protected $_option;

    /**
     * Category constructor.
     *
     * @param Options $options
     * @param Factory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param CategoryCollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Options $options,
        Factory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        CategoryCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_option           = $options;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @inheritdoc
     */
    public function getElementHtml()
    {
        $html = '<div class="admin__field-control admin__control-grouped" id="' . $this->getHtmlId() . '">';

        $html .= '<div id="category-select" class="admin__field" data-bind="scope:\'category\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="mp_category_ids" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= '<div class="admin__field admin__field-group-additional admin__field-small" 
                    data-bind="scope:\'create_category_button\'">';
        $html .= '<div class="admin__field-control">';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '</div></div></div>';

        $html .= '<!-- ko scope: \'create_category_modal\' --><!-- ko template: getTemplate() -->';
        $html .= '<!-- /ko --><!-- /ko -->';

        $html .= $this->getScriptHtml();

        return $html;
    }

    /**
     * Attach Slider Category suggest widget initialization
     *
     * @return string
     */
    public function getScriptHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "category": {
                                "component": "uiComponent",
                                "children": {
                                    "select_category": {
                                        "component": "Magento_Catalog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . json_encode($this->_option->toOptionArray()) . ',
                                            "value": ' . json_encode($this->getValues()) . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "slider_select_category",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            }                          
                        }
                    }
                }
            }
        </script>';

        return $html;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (!count($values)) {
            return [];
        }

        return $this->collectionFactory->create()->addIdFilter($values)->getAllIds();
    }
}
