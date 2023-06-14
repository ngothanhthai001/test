<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

namespace Amasty\PromoReports\Block\Adminhtml\Reports;

use Amasty\PromoReports\Model\Config\Source\DateRange;
use Amasty\PromoReports\Model\DateProxy;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Filters Form Handler
 */
class Filters extends Generic
{
    /**
     * Constants defined for names of filters form
     */
    public const ALL = 'all';
    public const STORE = 'store';
    public const CUSTOMER_GROUP = 'customer_group';
    public const DATE_RANGE = 'date_range';
    public const DATE_FROM = 'date_from';
    public const DATE_TO = 'date_to';
    public const SUBMIT = 'submit';

    /**
     * @var DataObject
     */
    private $objectConverter;

    /**
     * @var DateProxy
     */
    private $date;

    /**
     * @var DateRange
     */
    private $dateRange;

    /**
     * @var Collection
     */
    private $customerGroup;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        DataObject $objectConverter,
        DateProxy $date,
        DateRange $dateRange,
        Collection $customerGroup,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->objectConverter = $objectConverter;
        $this->date = $date;
        $this->dateRange = $dateRange;
        $this->customerGroup = $customerGroup;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('promo_reports_');
        $form->addField(
            self::STORE,
            'select',
            [
                'label'  => __('Store View:'),
                'title'  => __('Store View:'),
                'name'   => self::STORE,
                'class'  => 'ampromo-field',
                'values' => $this->getStoresArray()
            ]
        );

        $form->addField(
            self::CUSTOMER_GROUP,
            'select',
            [
                'label'  => __('Customer Group:'),
                'title'  => __('Customer Group:'),
                'name'   => self::CUSTOMER_GROUP,
                'class'  => 'ampromo-field',
                'values' => $this->getCustomerGroupsArray()
            ]
        )->addCustomAttribute('data-ampromo-js', 'data-customer-group');

        $form->addField(
            self::DATE_RANGE,
            'select',
            [
                'label'  => __('Date Range:'),
                'title'  => __('Date Range:'),
                'name'   => self::DATE_RANGE,
                'class'  => 'ampromo-field',
                'value'  => DateRange::LAST_DAY,
                'values' => $this->dateRange->toOptionArray()
            ]
        )->addCustomAttribute('data-ampromo-js', 'data-select');

        $form->addField(
            self::DATE_FROM,
            'date',
            [
                'label'       => __('From:'),
                'title'       => __('From:'),
                'name'        => self::DATE_FROM,
                'required'    => true,
                'readonly'    => true,
                'class'       => 'ampromo-field',
                'date_format' => 'M/d/Y',
                'value'       => $this->date->getDateWithOffsetByDays(-5),
                'max_date'    => $this->date->getDateWithOffsetByDays(0)
            ]
        )->addCustomAttribute('data-ampromo-js', 'date-range');

        $form->addField(
            self::DATE_TO,
            'date',
            [
                'label'       => __('To:'),
                'title'       => __('To:'),
                'name'        => self::DATE_TO,
                'required'    => true,
                'readonly'    => true,
                'class'       => 'ampromo-field',
                'date_format' => 'M/d/Y',
                'value'       => $this->date->getDateWithOffsetByDays(0),
                'max_date'    => $this->date->getDateWithOffsetByDays(0),
            ]
        )->addCustomAttribute('data-ampromo-js', 'date-range');

        $form->addField(
            self::SUBMIT,
            'button',
            [
                'value' => __('Refresh'),
                'title' => __('Refresh'),
                'name'  => self::SUBMIT,
                'class' => 'ampromo-field -refresh',
                'class' => 'abs-action-primary scalable',
            ]
        )->addCustomAttribute('data-ampromo-js', 'report-submit');

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    private function getCustomerGroupsArray()
    {
        $customerGroups = $this->objectConverter->toOptionArray(
            $this->groupRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems(),
            'id',
            'code'
        );

        array_unshift($customerGroups, ['value' => self::ALL, 'label' => __('All Customer Groups')]);

        return $customerGroups;
    }

    /**
     * @return array
     */
    private function getStoresArray()
    {
        $stores = $this->objectConverter->toOptionArray(
            $this->_storeManager->getStores(),
            'store_id',
            'name'
        );

        array_unshift($stores, ['value' => self::ALL, 'label' => __('All Stores')]);

        return $stores;
    }
}
