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

namespace Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Action;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Add
 * @package Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Action
 */
class Add extends AbstractRenderer
{
    /**
     * Render action
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        return '<button type="button" class="mp-ap-add-class" data-type="' . $row->getData('type') . '" data-class="'
            . $row->getData('class') . '">' . __('Add') . '</button>';
    }
}
