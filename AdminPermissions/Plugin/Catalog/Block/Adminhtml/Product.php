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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Product
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml
 */
class Product
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
     * @param \Magento\Catalog\Block\Adminhtml\Product $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSetLayout(\Magento\Catalog\Block\Adminhtml\Product $subject, $result)
    {
        if ($this->helperData->isEnabled() && !$this->helperData->isAllow('Mageplaza_AdminPermissions::product_create')
        ) {
            $subject->removeButton('add_new');
        }

        return $result;
    }
}
