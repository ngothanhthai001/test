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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Controller\Adminhtml\Product\Attribute;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Validate
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Controller\Adminhtml\Product\Attribute
 */
class Validate
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Validate constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate $validate
     * @param Json $result
     *
     * @return string
     */
    public function afterExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate $validate,
        $result
    ) {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        $attributeId = $validate->getRequest()->getParam('attribute_id');
        if (!$attributeId && !$this->helperData->isAllow('Mageplaza_AdminPermissions::product_attribute_create')) {
            $response = new DataObject([
                'error'                        => true,
                $validate::DEFAULT_MESSAGE_KEY => __('You don\'t have permission to create attribute')
            ]);
            $result->setJsonData($response->toJson());
        }

        return $result;
    }
}
