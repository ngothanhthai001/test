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

namespace Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes\Extended;

/**
 * Class MassActionCheckBox
 * @package Mageplaza\AdminPermissions\Block\Widget\Grid\Column\Renderer
 */
class MassActionCheckBox extends Extended
{
    /**
     * @param string $value Value of the element
     * @param bool $checked Whether it is checked
     *
     * @return string
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        $html = '<label class="data-grid-checkbox-cell-inner" ';
        $html .= ' for="id_' . $this->getColumn()->getName() . '_' . $this->escapeHtml($value) . '">';
        $html .= '<input type="checkbox" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= 'id="id_' . $this->getColumn()->getName() . '_' . $this->escapeHtml($value) . '" ';
        $html .= 'class="' .
            ($this->getColumn()->getInlineCss() ?: 'checkbox') .
            ' admin__control-checkbox' . '"';
        $html .= $checked . $this->getDisabled() . '/>';
        $html .= '<label for="id_' . $this->escapeHtml($value) . '"></label>';
        $html .= '</label>';

        return $html;
    }
}
