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

namespace Mageplaza\AdminPermissions\Plugin\User\Block;

use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Role
 * @package Mageplaza\AdminPermissions\Plugin\User\Block
 */
class Role
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
     * @param \Magento\User\Block\Role $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSetLayout(\Magento\User\Block\Role $subject, $result)
    {
        if ($this->helperData->isEnabled() && !$this->helperData->isAllow('Mageplaza_AdminPermissions::role_create')
        ) {
            $subject->removeButton('add');
        }

        return $result;
    }
}
