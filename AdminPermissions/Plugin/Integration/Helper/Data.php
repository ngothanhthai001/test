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

namespace Mageplaza\AdminPermissions\Plugin\Integration\Helper;

use Mageplaza\AdminPermissions\Helper\Data as HelperData;

/**
 * Class Data
 * @package Mageplaza\AdminPermissions\Plugin\Integration\Helper
 */
class Data
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Data constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Integration\Helper\Data $helper
     * @param array $resources
     *
     * @return array
     */
    public function beforeMapResources(\Magento\Integration\Helper\Data $helper, array $resources)
    {
        if ($this->helperData->isEnabled()) {
            return [$resources];
        }
        $restricted = $this->getRestrictedIds();
        foreach ($resources as $key => $resource) {
            if (strncmp($resource['id'], 'Mageplaza_AdminPermissions', 26) === 0
                && !in_array($resource['id'], $restricted, true)) {
                unset($resources[$key]);
            }
        }

        return [$resources];
    }

    /**
     * @return array
     */
    protected function getRestrictedIds()
    {
        return ['Mageplaza_AdminPermissions::configuration', 'Mageplaza_AdminPermissions::admin_permissions'];
    }
}
