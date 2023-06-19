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

namespace Mageplaza\AdminPermissions\Plugin\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\AdminPermissions\Plugin\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button
 */
class Save
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
     * @param \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save $subject
     * @param $result
     *
     * @return array
     */
    public function afterGetButtonData(
        \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save $subject,
        $result
    ) {
        if ($this->helperData->isEnabled() &&
            !$this->helperData->isAllow('Mageplaza_AdminPermissions::product_edit')
            && $subject->getProduct()->getId()
        ) {
            return [];
        }

        return $result;
    }
}
