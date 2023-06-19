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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class LimitCategory
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier
 */
class LimitCategory
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var CategoryCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DbHelper
     */
    private $dBHelper;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Collection constructor.
     *
     * @param Data $helperData
     * @param CategoryCollectionFactory $collectionFactory
     * @param DbHelper $dBHelper
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param LocatorInterface $locator
     */
    public function __construct(
        Data $helperData,
        CategoryCollectionFactory $collectionFactory,
        DbHelper $dBHelper,
        CategoryCollectionFactory $categoryCollectionFactory,
        LocatorInterface $locator
    ) {
        $this->helperData                = $helperData;
        $this->locator                   = $locator;
        $this->collectionFactory         = $collectionFactory;
        $this->dBHelper                  = $dBHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param Categories $categories
     * @param $result
     * @param $meta
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function afterModifyMeta(
        Categories $categories,
        $result,
        $meta
    ) {
        $category = $this->getCategoriesTree();

        $result['product-details']['children']['container_category_ids']
        ['children']['category_ids']['arguments']['data']['config']['options'] = $category;

        return $result;
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     *
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function getCategoriesTree($filter = null)
    {
        $storeId = (int) $this->locator->getStore()->getId();

        $categoriesTree = $this->retrieveCategoriesTree(
            $storeId,
            $this->retrieveShownCategoriesIds($storeId, (string) $filter)
        );

        return $categoriesTree;
    }

    /**
     * Retrieve filtered list of categories id.
     *
     * @param int $storeId
     * @param string $filter
     *
     * @return array
     * @throws LocalizedException
     */
    private function retrieveShownCategoriesIds(int $storeId, string $filter = ''): array
    {
        /* @var $matchingNamesCollection Collection */
        $matchingNamesCollection = $this->collectionFactory->create();

        if (!empty($filter)) {
            $matchingNamesCollection->addAttributeToFilter(
                'name',
                ['like' => $this->dBHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
            ->setStoreId($storeId);

        $shownCategoriesIds = [];

        /** @var CategoryModel $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        return $shownCategoriesIds;
    }

    /**
     * Retrieve tree of categories with attributes.
     *
     * @param int $storeId
     * @param array $shownCategoriesIds
     *
     * @return array|null
     * @throws LocalizedException
     */
    private function retrieveCategoriesTree(int $storeId, array $shownCategoriesIds): ?array
    {
        /* @var $collection Collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::category_view')) {
            $collection->getSelect()->where('0=1');
        }

        $adminPermission = $this->helperData->getAdminPermission();
        if ($adminPermission->getId()) {
            $this->helperData->filterCollection($adminPermission, $collection, 'category', 'entity_id');
        }

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value'    => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active']        = $category->getIsActive();
            $categoryById[$category->getId()]['label']            = $category->getName();
            $categoryById[$category->getId()]['__disableTmpl']    = true;
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }
}
