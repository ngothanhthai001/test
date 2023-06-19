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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml\Product\Attribute;

use Magento\Catalog\Block\Adminhtml\Product\Attribute;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Edit
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml\Product\Attribute
 */
class Edit
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Collection constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Attribute\Edit $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSetLayout(Attribute\Edit $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_edit')
            && $subject->getRequest()->getParam('attribute_id')
        ) {
            $subject->removeButton('save');
            $subject->removeButton('save_and_edit_button');
        }

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_delete')) {
            $subject->removeButton('delete');
        }

        return $result;
    }
}
