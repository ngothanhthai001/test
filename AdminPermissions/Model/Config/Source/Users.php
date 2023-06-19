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

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Mageplaza\AdminPermissions\Model\ResourceModel\User\CollectionFactory;

/**
 * Class Users
 * @package Mageplaza\AdminPermissions\Model\Config\Source
 */
class Users extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Users constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result     = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $user) {
            $result[$user->getId()] = $user->getName();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $result = [['value' => 0, 'label' => 'Please Select']];

        foreach ($this->toArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
