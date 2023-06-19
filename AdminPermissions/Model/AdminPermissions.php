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

namespace Mageplaza\AdminPermissions\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AdminPermissions
 * @package Mageplaza\AdminPermissions\Model
 * @method getMpStoreIds()
 * @method setRoleId($getId)
 * @method getMpCategoryIds()
 * @method getMpProductIds()
 * @method getMpCustomerIds()
 * @method getMpProdattrIds()
 * @method getMpUserRoleIds()
 * @method getMpPeriodDays()
 * @method getMpPeriodFrom()
 * @method getMpPeriodTo()
 * @method getMpLimitType()
 * @method getMpEnabled()
 * @method getMpSalesRestriction()
 * @method getMpCategoryRestriction()
 * @method getMpProductRestriction()
 * @method getMpCustomerRestriction()
 * @method getMpProdattrRestriction()
 * @method getMpUserRoleRestriction()
 * @method getMpCustomLimit()
 * @method getRoleId()
 * @method getMpCustomEnabled()
 */
class AdminPermissions extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_admin_permission';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_admin_permission';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_admin_permission';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AdminPermissions::class);
    }
}
