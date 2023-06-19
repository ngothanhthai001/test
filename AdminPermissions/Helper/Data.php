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

namespace Mageplaza\AdminPermissions\Helper;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Authorization\Model\ResourceModel\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Authorization\RoleLocatorInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AdminPermissions\Model\AdminPermissions;
use Mageplaza\AdminPermissions\Model\AdminPermissionsFactory;
use Mageplaza\AdminPermissions\Model\Config\Source\Product;
use Mageplaza\AdminPermissions\Model\Config\Source\Restriction;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions as AdminPermissionsResource;
use Mageplaza\AdminPermissions\Model\ResourceModel\Custom;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;

/**
 * Class Data
 * @package Mageplaza\AdminPermissions\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mp_admin_permission';
    const EMPTY_STORES       = [0];
    const ALL_PRODUCT        = 'all';

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var AdminPermissionsResource
     */
    protected $apResource;

    /**
     * @var AdminPermissionsFactory
     */
    protected $apModelFactory;

    /**
     * @var bool
     */
    protected $isForwarded = false;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var RoleLocatorInterface
     */
    protected $roleLocator;

    /**
     * @var RoleFactory
     */
    protected $roleFactory;

    /**
     * @var Role
     */
    protected $roleResource;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ClassesScanner
     */
    protected $classesScanner;

    /**
     * @var Custom
     */
    private $customResource;

    /**
     * @var State
     */
    private $state;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ViewInterface $view
     * @param Session $authSession
     * @param RoleLocatorInterface $roleLocator
     * @param RoleFactory $roleFactory
     * @param Role $roleResource
     * @param TimezoneInterface $timezone
     * @param AuthorizationInterface $authorization
     * @param AdminPermissionsResource $apResource
     * @param AdminPermissionsFactory $apModelFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ClassesScanner $classesScanner
     * @param State $state
     * @param Custom $customResource
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ViewInterface $view,
        Session $authSession,
        RoleLocatorInterface $roleLocator,
        RoleFactory $roleFactory,
        Role $roleResource,
        TimezoneInterface $timezone,
        AuthorizationInterface $authorization,
        AdminPermissionsResource $apResource,
        AdminPermissionsFactory $apModelFactory,
        ProductCollectionFactory $productCollectionFactory,
        ComponentRegistrar $componentRegistrar,
        ClassesScanner $classesScanner,
        State $state,
        Custom $customResource
    ) {
        $this->view                     = $view;
        $this->authSession              = $authSession;
        $this->timezone                 = $timezone;
        $this->authorization            = $authorization;
        $this->roleLocator              = $roleLocator;
        $this->roleFactory              = $roleFactory;
        $this->roleResource             = $roleResource;
        $this->apResource               = $apResource;
        $this->apModelFactory           = $apModelFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->componentRegistrar       = $componentRegistrar;
        $this->classesScanner           = $classesScanner;
        $this->customResource           = $customResource;
        $this->state                    = $state;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param Action $controller
     * @param RequestInterface $request
     */
    public function forwardToDeniedPage($controller, $request)
    {
        if ($this->isForwarded) {
            return;
        }
        $controller->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
        $this->view->loadLayout(['default', 'adminhtml_denied'], false, false, false);
        $this->view->renderLayout();
        $controller->getActionFlag()->set('', $controller::FLAG_NO_DISPATCH, true);
        $request->setDispatched();
        $this->isForwarded = true;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @param null $roleId
     *
     * @return AdminPermissions
     */
    public function getAdminPermission($roleId = null)
    {
        if ($roleId === null) {
            $roleId = $this->roleLocator->getAclRoleId();
            $role   = $this->roleFactory->create();
            $this->roleResource->load($role, $roleId);

            if ($role->getParentId()) {
                $roleId = $role->getParentId();
            }
        }
        $adminPermissions = $this->apModelFactory->create();
        $this->apResource->load($adminPermissions, $roleId, 'role_id');

        return $adminPermissions;
    }

    /**
     * @param AdminPermissions $adminPermissions
     *
     * @return bool
     */
    public function verifyTime($adminPermissions)
    {
        try {
            $periodDays = array_filter(explode(',', $adminPermissions->getMpPeriodDays()));
            if (empty($periodDays)) {
                return true;
            }
            $now = new DateTime('now', new DateTimeZone($this->timezone->getConfigTimezone()));
            if (!in_array($now->format('l'), $periodDays, true)) {
                return false;
            }
            [$fromH, $fromM, $fromS] = explode(',', $adminPermissions->getMpPeriodFrom());
            $from = (new DateTime('now', new DateTimeZone($this->timezone->getConfigTimezone())))
                ->setTime($fromH, $fromM, $fromS);

            [$toH, $toM, $toS] = explode(',', $adminPermissions->getMpPeriodTo());
            $to   = (new DateTime('now', new DateTimeZone($this->timezone->getConfigTimezone())))
                ->setTime($toH, $toM, $toS);
            $type = $adminPermissions->getMpLimitType();

            return (bool) $type === !($now < $from || $now > $to);
        } catch (Exception $e) {
            $this->_logger->critical($e);

            return true;
        }
    }

    /**
     * @return bool
     */
    public function isPermissionEnabled()
    {
        $adminPermission = $this->getAdminPermission();

        return $this->isEnabled() && $adminPermission->getId();
    }

    /**
     * @return array
     */
    public function getAllStoreIds()
    {
        return array_keys($this->storeManager->getStores());
    }

    /**
     * @param AdminPermissions $adminPermission
     *
     * @return array
     */
    public function getAllowedRestrictionStoreIds($adminPermission)
    {
        $restriction         = $adminPermission->getMpSalesRestriction();
        $allStoreIds         = $this->getAllStoreIds();
        $restrictionStoreIds = array_filter(explode(',', $adminPermission->getMpStoreIds()));
        $allowStoreIds       = [];
        switch ($restriction) {
            case Restriction::NO:
                return [];
            case Restriction::ALLOW:
                $allowStoreIds = !empty($restrictionStoreIds) ? $restrictionStoreIds : self::EMPTY_STORES;
                break;
            case Restriction::DENY:
                $allowStoreIds = array_diff($allStoreIds, $restrictionStoreIds);
                $allowStoreIds = !empty($allowStoreIds) ? $allowStoreIds : self::EMPTY_STORES;
                break;
        }

        return $allowStoreIds;
    }

    /**
     * @param string $adminResource
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isAllow($adminResource)
    {
        if (!$this->getRequest()->getActionName()) {
            return true;
        }

        return $this->authorization->isAllowed($adminResource) || ($this->state->getAreaCode() === Area::AREA_ADMINHTML
                && $this->roleLocator->getAclRoleId() === null);
    }

    /**
     * @param AdminPermissions $adminPermission
     *
     * @return array|string|null
     */
    public function getProductIds($adminPermission)
    {
        $applyFor   = $adminPermission->getMpProductApplyFor();
        $productIds = [];
        $user       = $this->authSession->getUser();
        if (!$user) {
            return null;
        }
        switch ($applyFor) {
            case Product::ALL:
                return self::ALL_PRODUCT;
            case Product::SPECIFIC:
                $productIds = array_filter(explode(',', $adminPermission->getMpProductIds()));
                break;
            case Product::OWN_CREATE:
                $productIds = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('mp_product_owner')
                    ->addFieldToFilter('mp_product_owner', $user->getId())->getAllIds();
                break;
            case Product::USER_IN_SAME_ROLE:
                $roleIds    = $user->getRole()->getRoleUsers();
                $productIds = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('mp_product_owner')
                    ->addFieldToFilter('mp_product_owner', ['in' => $roleIds])->getAllIds();
                break;
        }

        return $productIds;
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function aggregateCustomTable()
    {
        $modulePaths       = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        $classesList       = [];
        $excludedPathsList = [
            'application' => $this->getExcludedModulePaths($modulePaths),
        ];
        $this->classesScanner->addExcludePatterns($excludedPathsList);
        foreach ($modulePaths as $path) {
            $classesList = array_merge($classesList, $this->classesScanner->getList($path));
        }

        $oldClass = $this->customResource->getAllClass();

        $modelClass      = [];
        $controllerClass = [];
        foreach ($classesList as $className) {
            if (!$className || in_array($className, $oldClass, true)
            ) {
                continue;
            }
            if (is_subclass_of($className, AbstractModel::class)) {
                $modelClass[] = [
                    'type'  => 'model',
                    'class' => $className
                ];
            }
            if (is_subclass_of($className, AbstractAction::class)) {
                $controllerClass[] = [
                    'type'  => 'controller',
                    'class' => $className
                ];
            }
        }
        $data = array_merge($modelClass, $controllerClass);
        if (!empty($data)) {
            $this->customResource->getConnection()->insertMultiple($this->customResource->getMainTable(), $data);
        }

        $this->customResource->getConnection()->delete(
            $this->customResource->getMainTable(),
            ['class' => ['nin' => $classesList]]
        );
    }

    /**
     * Build list of module path regexps which should be excluded from compilation
     *
     * @param string[] $modulePaths
     *
     * @return string[]
     */
    private function getExcludedModulePaths(array $modulePaths)
    {
        $modulesByBasePath = [];
        foreach ($modulePaths as $modulePath) {
            $moduleDir                                  = basename($modulePath);
            $vendorPath                                 = dirname($modulePath);
            $vendorDir                                  = basename($vendorPath);
            $basePath                                   = dirname($vendorPath);
            $modulesByBasePath[$basePath][$vendorDir][] = $moduleDir;
        }

        $basePathsRegExps = [];
        foreach ($modulesByBasePath as $basePath => $vendorPaths) {
            $vendorPathsRegExps = [];
            foreach ($vendorPaths as $vendorDir => $vendorModules) {
                $vendorPathsRegExps[] = $vendorDir . '/(?:' . implode('|', $vendorModules) . ')';
            }
            $basePathsRegExps[] = preg_quote($basePath, '#') . '/(?:' . implode('|', $vendorPathsRegExps) . ')';
        }

        $excludedModulePaths = [
            '#^(?:' . implode('|', $basePathsRegExps) . ')/Test#',
            '#^(?:' . implode('|', $basePathsRegExps) . ')/tests#',
        ];

        return $excludedModulePaths;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * @param AdminPermissions $adminPermission
     * @param AbstractDb $collection
     * @param string $field
     * @param string $entity
     */
    public function filterCollection($adminPermission, $collection, $field, $entity)
    {
        $restriction = $adminPermission->getData('mp_' . $field . '_restriction');
        $ids         = array_filter(explode(',', $adminPermission->getData('mp_' . $field . '_ids')));

        if (!empty($ids)) {
            switch ($restriction) {
                case Restriction::NO:
                    break;
                case Restriction::ALLOW:
                    $collection->addFieldToFilter($entity, ['in' => $ids]);
                    break;
                case Restriction::DENY:
                    $collection->addFieldToFilter($entity, ['nin' => $ids]);
                    break;
            }
        }
    }

    /**
     * @param array $arg
     * @throws LocalizedException
     */
    public function checkForward($arg)
    {
        [$adminResource, $id, $field, $controller, $request] = $arg;
        $allowAction = $this->isAllow($adminResource);
        if (!$allowAction) {
            $this->forwardToDeniedPage($controller, $request);
        }
        $adminPermission = $this->getAdminPermission();
        if (!$adminPermission->getId()) {
            return;
        }
        $restriction = $adminPermission->getData('mp_' . $field . '_restriction');
        $ids         = array_filter(explode(',', $adminPermission->getData('mp_' . $field . '_ids')));
        switch ($restriction) {
            case Restriction::NO:
                return;
            case Restriction::ALLOW:
                if (!in_array($id, $ids, true)) {
                    $this->forwardToDeniedPage($controller, $request);
                }
                break;
            case Restriction::DENY:
                if (in_array($id, $ids, true)) {
                    $this->forwardToDeniedPage($controller, $request);
                }
                break;
        }
    }
}
