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

namespace Mageplaza\AdminPermissions\Observer\Category;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Edit
 * @package Mageplaza\AdminPermissions\Observer\Category
 */
class Edit extends AbstractCategory
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    protected $adminResource = 'Mageplaza_AdminPermissions::category_view';

    /**
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if ($this->storeManager === null) {
            $this->storeManager = $this->helperData->getStoreManager();
        }

        return $this->storeManager;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getCategoryId($request)
    {
        $categoryId = (string) $request->getParam('id');
        $storeId    = (int) $request->getParam('store');

        if (!$categoryId) {
            if ($storeId) {
                $categoryId = (string) $this->getStoreManager()->getStore($storeId)->getRootCategoryId();
            } else {
                $defaultStoreView = $this->getStoreManager()->getDefaultStoreView();
                if ($defaultStoreView) {
                    $categoryId = (string) $defaultStoreView->getRootCategoryId();
                } else {
                    $stores = $this->getStoreManager()->getStores();
                    if (count($stores)) {
                        $store      = reset($stores);
                        $categoryId = (string) $store->getRootCategoryId();
                    }
                }
            }
        }

        return $categoryId;
    }
}
