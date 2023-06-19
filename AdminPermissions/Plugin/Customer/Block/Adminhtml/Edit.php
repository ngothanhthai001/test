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

namespace Mageplaza\AdminPermissions\Plugin\Customer\Block\Adminhtml;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Edit
 * @package Mageplaza\AdminPermissions\Plugin\Customer\Block\Adminhtml
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
     * @param \Magento\Customer\Block\Adminhtml\Edit $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSetLayout(\Magento\Customer\Block\Adminhtml\Edit $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::customer_edit') && $subject->getCustomerId()
        ) {
            $subject->removeButton('save');
            $subject->removeButton('reset');
            $subject->removeButton('save_and_continue');
        }

        return $result;
    }
}
