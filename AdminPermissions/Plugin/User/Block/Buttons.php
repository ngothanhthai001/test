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

namespace Mageplaza\AdminPermissions\Plugin\User\Block;

use Magento\Framework\View\Element\Template;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Buttons
 * @package Mageplaza\AdminPermissions\Plugin\User\Block
 */
class Buttons
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Collection constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\User\Block\Buttons $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSetLayout(\Magento\User\Block\Buttons $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        /** @var Template $toolbar */
        $toolbar = $subject->getToolbar();
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::role_edit')
            && $subject->getRequest()->getParam('rid')
        ) {
            $toolbar->unsetChild('saveButton');
        }
        if (!$this->helperData->isAllow('Mageplaza_AdminPermissions::role_delete')
        ) {
            $toolbar->unsetChild('deleteButton');
        }

        return $result;
    }
}
