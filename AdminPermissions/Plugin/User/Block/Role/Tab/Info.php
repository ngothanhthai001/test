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

namespace Mageplaza\AdminPermissions\Plugin\User\Block\Role\Tab;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\User\Block\Role\Tab\Info as InfoTab;
use Mageplaza\AdminPermissions\Helper\Data;
use Mageplaza\AdminPermissions\Model\AdminPermissions as AdminPermissionsModel;
use Mageplaza\AdminPermissions\Model\Config\Source\DaysOfWeek;
use Mageplaza\AdminPermissions\Model\Config\Source\LimitType;

/**
 * Class Info
 * @package Mageplaza\AdminPermissions\Plugin\User\Block\Role\Tab
 */
class Info
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var LimitType
     */
    private $limitType;

    /**
     * @var DaysOfWeek
     */
    private $daysOfWeek;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var AdminPermissionsModel
     */
    protected $_object;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Select constructor.
     *
     * @param TimezoneInterface $timezone
     * @param RequestInterface $request
     * @param LimitType $limitType
     * @param DaysOfWeek $daysOfWeek
     * @param Data $helperData
     */
    public function __construct(
        TimezoneInterface $timezone,
        RequestInterface $request,
        LimitType $limitType,
        DaysOfWeek $daysOfWeek,
        Data $helperData
    ) {
        $this->timezone   = $timezone;
        $this->request    = $request;
        $this->limitType  = $limitType;
        $this->daysOfWeek = $daysOfWeek;
        $this->helperData = $helperData;
    }

    /**
     * @param InfoTab $subject
     * @param $result
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function after_beforeToHtml(
        InfoTab $subject,
        $result
    ) {
        if (!$this->helperData->isEnabled()
            || !$this->helperData->isAllow('Mageplaza_AdminPermissions::admin_permissions')) {
            return $result;
        }

        $form     = $subject->getForm();
        $fieldset = $form->addFieldset('mp_valid_time_fieldset', ['legend' => __('Valid Time')]);

        $fieldset->addField('mp_limit_type', 'select', [
            'name'   => 'mp_limit_type',
            'label'  => __('Type'),
            'title'  => __('Type'),
            'value'  => 1,
            'values' => $this->limitType->toOptionArray()
        ]);
        $fieldset->addField('mp_period_days', 'multiselect', [
            'name'   => 'mp_period_days',
            'label'  => __('Select Day(s)'),
            'title'  => __('Select Day(s)'),
            'values' => $this->daysOfWeek->toOptionArray()
        ]);
        $dateFormat = $this->timezone->getDateFormat();
        $fieldset->addField('mp_period_from_date', 'date', [
            'name'         => 'mp_period_from_date',
            'label'        => __('Apply From Date'),
            'title'        => __('Apply From Date'),
            'input_format' => DateTime::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ]);
        $fieldset->addField('mp_period_to_date', 'date', [
            'name'         => 'mp_period_to_date',
            'label'        => __('Apply To Date'),
            'title'        => __('Apply To Date'),
            'input_format' => DateTime::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ]);
        $fieldset->addField('mp_period_from', 'time', [
            'name'  => 'mp_period_from',
            'label' => __('From'),
            'title' => __('From'),
        ]);
        $fieldset->addField('mp_period_to', 'time', [
            'name'  => 'mp_period_to',
            'label' => __('To'),
            'title' => __('To'),
        ]);

        $subject->getForm()->addValues($this->getObject()->getData());

        return $result;
    }

    /**
     * @return AdminPermissionsModel
     */
    protected function getObject()
    {
        if ($this->_object === null) {
            $this->_object = $this->helperData->getAdminPermission($this->request->getParam('rid') ?: '');
        }

        return $this->_object;
    }
}
