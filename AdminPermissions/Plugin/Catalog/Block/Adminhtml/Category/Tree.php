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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml\Category;

use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Tree\Node;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;

/**
 * Class Tree
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Block\Adminhtml\Category
 */
class Tree
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    private $tree;

    /**
     * Collection constructor.
     *
     * @param Session $authSession
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $tree
     * @param Data $helperData
     */
    public function __construct(
        Session $authSession,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $tree,
        Data $helperData
    ) {
        $this->authSession = $authSession;
        $this->helperData  = $helperData;
        $this->tree        = $tree;
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Category\Tree $tree
     * @param Node $root
     *
     * @return Node
     */
    public function afterGetRoot(\Magento\Catalog\Block\Adminhtml\Category\Tree $tree, $root)
    {
        $adminPermissions = $this->helperData->getAdminPermission();
        if (!$this->helperData->isPermissionEnabled()) {
            return $root;
        }

        $allowView = $this->helperData->isAllow('Mageplaza_AdminPermissions::category_view');
        $treeRoot  = $this->tree->load(null, 3)->getNodeById(Category::TREE_ROOT_ID);
        if (!$allowView) {
            return $treeRoot;
        }

        $restriction = $adminPermissions->getMpCategoryRestriction();

        $categoryIds = array_filter(explode(',', $adminPermissions->getMpCategoryIds()));
        $isRootCat   = (int) $root->getId() !== Category::TREE_ROOT_ID;
        switch ($restriction) {
            case Restriction::NO:
                return $root;
            case Restriction::ALLOW:
                if ($isRootCat && !in_array($root->getId(), $categoryIds, true)) {
                    $root = $treeRoot;
                }
                break;
            case Restriction::DENY:
                if ($isRootCat && in_array($root->getId(), $categoryIds, true)) {
                    $root = $treeRoot;
                }
                break;
        }

        return $root;
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Category\Tree $tree
     * @param $treeJson
     *
     * @return string
     */
    public function afterGetTreeJson(
        \Magento\Catalog\Block\Adminhtml\Category\Tree $tree,
        $treeJson
    ) {
        $adminPermissions = $this->helperData->getAdminPermission();
        if (!$this->helperData->isPermissionEnabled()) {
            return $treeJson;
        }
        $allowView = $this->helperData->isAllow('Mageplaza_AdminPermissions::category_view');

        if (!$allowView) {
            return '[]';
        }
        $restriction = $adminPermissions->getMpCategoryRestriction();
        $categoryIds = array_filter(explode(',', $adminPermissions->getMpCategoryIds()));
        $treeArray   = Data::jsonDecode($treeJson);

        switch ($restriction) {
            case Restriction::NO:
                return $treeJson;
            case Restriction::ALLOW:
                $this->removeNotAllowCategory($treeArray, $categoryIds);
                break;
            case Restriction::DENY:
                $this->removeNotAllowCategory($treeArray, $categoryIds, false);
                break;
        }

        $treeJson = Data::jsonEncode($treeArray);

        return $treeJson;
    }

    /**
     * @param array $treeArray
     * @param $categoryIds
     * @param bool $allow
     *
     * @return array
     */
    protected function removeNotAllowCategory(array &$treeArray, $categoryIds, $allow = true)
    {
        foreach ($treeArray as $key => &$node) {
            if (in_array($node['id'], $categoryIds, true) !== $allow) {
                unset($treeArray[$key]);
                continue;
            }
            if (isset($node['children']) && is_array($node['children'])) {
                $this->removeNotAllowCategory($node['children'], $categoryIds, $allow);
            }
        }
        unset($node);
        $treeArray = array_values($treeArray);

        return $treeArray;
    }
}
