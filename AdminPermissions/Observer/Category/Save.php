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

/**
 * Class Save
 * @package Mageplaza\AdminPermissions\Observer\Category
 */
class Save extends AbstractCategory
{
    /**
     * @var string
     */
    protected $adminResource = 'Mageplaza_AdminPermissions::category_edit';

    /**
     * @param RequestInterface $request
     *
     * @return mixed|string
     */
    protected function getCategoryId($request)
    {
        return $request->getParam('entity_id');
    }
}
