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

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class CustomerGroup
 * @package Mageplaza\AdminPermissions\Model\Config\Source
 */
class CustomerGroup extends AbstractSource
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * CustomerGroup constructor.
     *
     * @param ModuleManager $moduleManager
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ModuleManager $moduleManager,
        CollectionFactory $collectionFactory
    ) {
        $this->moduleManager     = $moduleManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [];
        foreach ($this->collectionFactory->create() as $group) {
            $customerGroups[$group->getId()] = $group->getCode();
        }

        return $customerGroups;
    }
}
