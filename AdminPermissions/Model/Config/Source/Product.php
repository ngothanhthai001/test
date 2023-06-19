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

namespace Mageplaza\AdminPermissions\Model\Config\Source;

/**
 * Class Product
 * @package Mageplaza\AdminPermissions\Model\Config\Source
 */
class Product extends AbstractSource
{
    const ALL               = 'all';
    const SPECIFIC          = 'specific';
    const OWN_CREATE        = 'own_create';
    const USER_IN_SAME_ROLE = 'same_role';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ALL               => __('All'),
            self::SPECIFIC          => __('Specific Products'),
            self::OWN_CREATE        => __('Products created by this user'),
            self::USER_IN_SAME_ROLE => __('Products created by the same-role users'),
        ];
    }
}
