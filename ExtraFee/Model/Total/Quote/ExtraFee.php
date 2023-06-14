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
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Model\Total\Quote;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Model\Calculation;
use Mageplaza\ExtraFee\Helper\Data;
use Mageplaza\ExtraFee\Model\Config\Source\ApplyType;
use Mageplaza\ExtraFee\Model\Config\Source\CalculateOptions;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayArea;
use Mageplaza\ExtraFee\Model\Config\Source\FeeType;
use Mageplaza\ExtraFee\Model\Rule;
use Mageplaza\ExtraFee\Model\RuleFactory;
use Magento\Backend\Model\Session\Quote as BackendModelSession;

/**
 * Class ExtraFee
 * @package Mageplaza\ExtraFee\Model\Total\Quote
 */
class ExtraFee extends AbstractTotal
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var
     */
    protected $addressToValidate;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var BackendModelSession
     */
    protected $backendModelSession;

    /**
     * @param BackendModelSession $backendModelSession
     * @param Data $helper
     * @param PriceCurrencyInterface $priceCurrency
     * @param Calculation $calculation
     * @param CustomerSession $customerSession
     * @param RequestInterface $request
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        BackendModelSession $backendModelSession,
        Data $helper,
        PriceCurrencyInterface $priceCurrency,
        Calculation $calculation,
        CustomerSession $customerSession,
        RequestInterface $request,
        RuleFactory $ruleFactory

    ) {
        $this->backendModelSession = $backendModelSession;
        $this->helper              = $helper;
        $this->priceCurrency       = $priceCurrency;
        $this->calculation         = $calculation;
        $this->customerSession     = $customerSession;
        $this->ruleFactory         = $ruleFactory;
        $this->request             = $request;
    }

    /**
     * Collect extra fee totals
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this
     * @throws Exception
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $fullActionName = $this->request->getFullActionName();

        if (
            !$this->helper->isEnabled()
            || ($quote->isVirtual() && $this->_getAddress()->getAddressType() === Address::ADDRESS_TYPE_SHIPPING)
            || (!$quote->isVirtual() && $this->_getAddress()->getAddressType() === Address::ADDRESS_TYPE_BILLING)
        ) {
            return $this;
        }

        if (in_array($fullActionName, [
            'multishipping_checkout_overview',
            'multishipping_checkout_overviewPost'
        ], true)) {
            /**
             * Reset amounts
             */
            $this->_setAmount(0);
            $this->_setBaseAmount(0);

            return $this;
        }

        $area = $this->helper->getCheckoutSession()->getMpArea();
        if ($area) {
            $this->getApplyRule($quote, $area);
        }
        $this->calculateAutoExtraFee($quote);

        if (empty($this->helper->getMpExtraFee($quote))) {
            return $this;
        }

        $extraFee  = $this->helper->getMpExtraFee($quote);
        $applyRule = $this->getAllApplyRule($quote);

        foreach ($extraFee as $ruleId => $option) {
            if (!isset($applyRule[$ruleId])) {
                continue;
            }
            /** @var Rule $rule */
            $rule     = $this->ruleFactory->create()->load($ruleId);
            $options  = $rule->getOptions() ? Data::jsonDecode($rule->getOptions())['option']['value'] : [];
            $taxClass = $rule->getFeeTax();

            if (is_array($option)) {
                foreach ($option as $item) {
                    [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax]
                        = $this->calculateExtraFeeAmount($quote, $options[$item], $taxClass);
                    $this->setCode("mp_extra_fee_rule_{$ruleId}_{$item}");
                    $this->_addAmount($ruleFeeAmountInclTax);
                    $this->_addBaseAmount($baseRuleFeeAmountInclTax);
                }
            } else {
                [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax]
                    = $this->calculateExtraFeeAmount($quote, $options[$option], $taxClass);
                $this->setCode("mp_extra_fee_rule_{$ruleId}_{$option}");
                $this->_addAmount(round($ruleFeeAmountInclTax, 2));
                $this->_addBaseAmount(round($baseRuleFeeAmountInclTax, 2));
            }
        }

        return $this;
    }

    /**
     * @param Quote $quote
     * @param mixed $area
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getApplyRule($quote, $area)
    {
        $area            = explode(',', $area);
        $applyRule       = [];
        $selectedOptions = [];
        foreach ($area as $item) {
            list($applyRule[], $selectedOptions[]) = $this->checkApplyRule($quote, $item);
        }
        $this->helper->getCheckoutSession()->setMpExtraFee([$applyRule, $selectedOptions]);
    }

    /**
     * @param Quote|Address $quote
     * @param mixed $area
     * @param bool $isMultiShipping
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkApplyRule($quote, $area, $isMultiShipping = false)
    {
        $backendModelSession = $this->backendModelSession->getQuote();
        $ruleCollection      = $this->ruleFactory->create()->getCollection()->setOrder('priority', 'ASC');
        $defaults            = [];
        $applyRule           = [];

        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $stores         = explode(',', $rule->getStoreIds());
            $customerGroups = explode(',', $rule->getCustomerGroups());
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            } elseif ($backendModelSession->getId()) {
                $customerGroupId = $backendModelSession->getCustomerGroupId();
            } else {
                $customerGroupId = 0;
            }

            if (
                !$rule->getStatus()
                || !in_array($customerGroupId, $customerGroups, false)
                || !(in_array($quote->getStoreId(), $stores, false) || in_array('0', $stores, true))
            ) {
                continue;
            }
            $address = $quote;
            if (!$isMultiShipping) {
                $address = $this->getAddressToValidate($quote);
            }

            if ($rule->validate($address)) {
                if ($rule->getArea() === $area) {
                    $default = Data::jsonDecode($rule->getOptions())['default'];
                    if ($default) {
                        $defaults[$rule->getId()] = $default[0];
                    }
                }
                if ((int) $rule->getApplyType() === ApplyType::AUTOMATIC || $rule->getArea() !== $area) {
                    if ($rule->getStopFurtherProcessing()) {
                        break;
                    }
                    continue;
                }

                $options = Data::jsonDecode($rule->getOptions());
                if (
                    !isset($options['option'])
                    || empty($options['option']['value'])
                    || !is_array($options['option']['value'])
                ) {
                    continue;
                }
                foreach ($options['option']['value'] as &$option) {
                    [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax]
                        = $this->calculateExtraFeeAmount($quote, $option, $rule->getFeeTax());
                    $option['calculated_amount']          = $ruleFeeAmount;
                    $option['calculated_amount_incl_tax'] = $ruleFeeAmountInclTax;
                }
                unset($option);
                $rule->setOptions(Data::jsonEncode($options));

                $applyRule[] = $rule->getData();

                if ($rule->getStopFurtherProcessing()) {
                    break;
                }
            }
        }
        if (!$this->helper->getMpExtraFee($quote, $area)) {
            $this->helper->setMpExtraFee($quote, http_build_query(['rule' => $defaults]), $area);
        }

        $selectedOptions = $this->helper->getMpExtraFee($quote, $area);

        usort($applyRule, function ($aSort, $bSort) {
            return ($aSort['sort_order'] <= $bSort['sort_order']) ? -1 : 1;
        });

        return [$applyRule, $selectedOptions];
    }

    /**
     * @param Quote $quote
     *
     * @return Address
     */
    protected function getAddressToValidate($quote)
    {
        if (
            !$this->addressToValidate
            || ($this->addressToValidate && !$this->addressToValidate->getData('total_qty'))
        ) {
            $this->addressToValidate =
                clone ($quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress());
        }

        $defaultCountryId = $this->helper->getDefaultCountryId();
        $defaultRegionId  = $this->helper->getDefaultRegionId();
        $defaultPostcode  = $this->helper->getDefaultPostcode();

        if (!$this->addressToValidate->getCountryId()) {
            $this->addressToValidate->setCountryId($defaultCountryId);
        }

        if (!$this->addressToValidate->getRegionId()) {
            $this->addressToValidate->setRegionId($defaultRegionId);
        }

        if (!$this->addressToValidate->getPostcode()) {
            $this->addressToValidate->setPostcode($defaultPostcode);
        }

        return $this->addressToValidate;
    }

    /**
     * @param Quote|Address $quote
     * @param array|Rule $option
     * @param int $taxClass
     *
     * @return array
     */
    public function calculateExtraFeeAmount($quote, $option, $taxClass)
    {
        if (is_object($option)) {
            $type   = $option->getFeeType();
            $amount = $option->getAmount();
        } else {
            $type   = $option['type'];
            $amount = $option['amount'];
        }

        if ($this->customerSession->isLoggedIn()) {
            $customer         = $this->customerSession->getCustomer();
            $customerId       = $customer->getId();
            $customerTaxClass = $customer->getTaxClassId();
        } else {
            $customerId       = null;
            $customerTaxClass = null;
        }
        $rateRequest = $this->calculation->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $customerTaxClass,
            $quote->getStoreId(),
            $customerId
        );

        $rateRequest->setProductClassId($taxClass);
        $taxRate = $this->calculation->getRate($rateRequest);

        switch ($type) {
            case FeeType::FIX_AMOUNT_FOR_WHOLE_CART:
                $baseRuleFeeAmount = $amount;
                break;
            case FeeType::PERCENTAGE_OF_CART_TOTAL:
                $calculateOptions  = $this->helper->getConfigGeneral('calculate_options')
                    ? explode(',', $this->helper->getConfigGeneral('calculate_options'))
                    : [];
                $baseRuleFeeAmount = $quote->getBaseSubtotal();
                if ($quote instanceof Address) {
                    $shippingAmount      = $quote->getBaseShippingAmount();
                    $baseShippingInclTax = $quote->getBaseShippingInclTax();
                } else {
                    $shippingAmount      = $quote->getShippingAddress()->getBaseShippingAmount();
                    $baseShippingInclTax = $quote->getShippingAddress()->getBaseShippingInclTax();
                }

                if (in_array(CalculateOptions::TAX, $calculateOptions, false)) {
                    $baseRuleFeeAmount = $quote->getBaseSubtotal() + ($quote->getBaseSubtotal() * $taxRate) / 100;
                    $shippingAmount    = $baseShippingInclTax;
                }
                if (in_array(CalculateOptions::DISCOUNT, $calculateOptions, false)) {
                    $baseRuleFeeAmount += $quote->getBaseSubtotalWithDiscount() - $quote->getBaseSubtotal();
                }
                if (in_array(CalculateOptions::SHIPPING_FEE, $calculateOptions, false)) {
                    $baseRuleFeeAmount += $shippingAmount;
                }
                $baseRuleFeeAmount *= ($amount / 100);
                break;
            default:
                $qtyOrdered = $quote->getItemsQty();
                if ($quote instanceof Address) {
                    $qtyOrdered = $quote->getItemQty();
                    if (!$qtyOrdered && $quote->getQuote()->hasVirtualItems()) {
                        foreach ($quote->getAllVisibleItems() as $item) {
                            $qtyOrdered += $item->getQty();
                        }
                    }
                }

                $baseRuleFeeAmount = $amount * $qtyOrdered;
                break;
        }
        $baseRuleFeeAmountInclTax = $baseRuleFeeAmount + ($baseRuleFeeAmount * $taxRate / 100);
        $ruleFeeAmount            = $this->priceCurrency->convert($baseRuleFeeAmount, $quote->getStore());
        $ruleFeeAmountInclTax     = $this->priceCurrency->convert($baseRuleFeeAmountInclTax, $quote->getStore());

        return [
            round($baseRuleFeeAmount, 2),
            round($ruleFeeAmount, 2),
            round($baseRuleFeeAmountInclTax, 2),
            round($ruleFeeAmountInclTax, 2)
        ];
    }

    /**
     * @param Quote|Address $quote
     * @param bool $isFetch
     * @param bool $isMultiShipping
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function calculateAutoExtraFee($quote, $isFetch = false, $isMultiShipping = false)
    {
        $backendModelSession = $this->backendModelSession->getQuote();
        $ruleCollection      = $this->ruleFactory->create()->getCollection()
            ->setOrder('priority', 'ASC');

        $result = [];
        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $stores         = explode(',', $rule->getStoreIds());
            $customerGroups = explode(',', $rule->getCustomerGroups());
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            } elseif ($backendModelSession->getId()) {
                $customerGroupId = $backendModelSession->getCustomerGroupId();
            } else {
                $customerGroupId = 0;
            }

            if (!$isMultiShipping) {
                $storeId = $quote->getStoreId();
            } else {
                $storeId = $quote->getQuote()->getStoreId();
            }

            if (
                !$rule->getStatus()
                || !in_array($customerGroupId, $customerGroups, false)
                || !(in_array($storeId, $stores, false) || in_array('0', $stores, true))
            ) {
                continue;
            }

            $address = $quote;
            if (!$isMultiShipping) {
                $address = $this->getAddressToValidate($quote);
            }

            /** product special = product category IdBook and not visible */
           // $checkProductSpecial =  $this->helper->checkProductSpecial($quote);
            if ($rule->validate($address)) {
                if ((int) $rule->getApplyType() !== ApplyType::AUTOMATIC) {
                    if ($rule->getStopFurtherProcessing()) {
                        break;
                    }
                    continue;
                }

                $taxClass = $rule->getFeeTax();
                $rule->setType($rule->getFeeType());
                [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax]
                    = $this->calculateExtraFeeAmount($quote, $rule, $taxClass);
                if ($isFetch) {
                    $result[] = $this->fetchAutoFee(
                        $rule,
                        $baseRuleFeeAmount,
                        $ruleFeeAmount,
                        $baseRuleFeeAmountInclTax,
                        $ruleFeeAmountInclTax,
                        $quote
                    );
                } else {
                    $this->addAutoFeeToTotal($rule, $ruleFeeAmountInclTax, $baseRuleFeeAmountInclTax);
                }
                if ($rule->getStopFurtherProcessing()) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param Rule $rule
     * @param float $baseRuleFeeAmount
     * @param float $ruleFeeAmount
     * @param float $baseRuleFeeAmountInclTax
     * @param float $ruleFeeAmountInclTax
     * @param Quote $quote
     *
     * @return array
     */
    protected function fetchAutoFee(
        $rule,
        $baseRuleFeeAmount,
        $ruleFeeAmount,
        $baseRuleFeeAmountInclTax,
        $ruleFeeAmountInclTax,
        $quote
    ) {
        $this->helper->getConfigValue('');
        $label = isset(Data::jsonDecode($rule->getLabels())[$quote->getStoreId()])
            ? Data::jsonDecode($rule->getLabels())[$quote->getStoreId()] : $rule->getName();

        return [
            'code'                => "mp_extra_fee_rule_{$rule->getId()}_auto",
            'title'               => __($label),
            'label'               => __($label),
            'value'               => $ruleFeeAmount,
            'value_excl_tax'      => $ruleFeeAmount,
            'value_incl_tax'      => $ruleFeeAmountInclTax,
            'base_value'          => $baseRuleFeeAmount,
            'base_value_incl_tax' => $baseRuleFeeAmountInclTax,
            'rf'                  => $rule->getRefundable(),
            'display_area'        => '3',
            'apply_type'          => $rule->getApplyType(),
            'rule_label'          => $this->helper->getRuleLabel($rule, $quote->getStoreId())
        ];
    }

    /**
     * @param Rule $rule
     * @param float $ruleFeeAmount
     * @param float $baseRuleFeeAmount
     */
    protected function addAutoFeeToTotal($rule, $ruleFeeAmount, $baseRuleFeeAmount)
    {
        $this->setCode("mp_extra_fee_rule_{$rule->getId()}_auto");
        $this->_addAmount($ruleFeeAmount);
        $this->_addBaseAmount($baseRuleFeeAmount);
    }

    /**
     * @param Quote $quote
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllApplyRule($quote)
    {
        $backendModelSession = $this->backendModelSession->getQuote();
        $ruleCollection      = $this->ruleFactory->create()->getCollection()->setOrder('priority', 'ASC');
        $applyRule           = [];

        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $stores         = explode(',', $rule->getStoreIds());
            $customerGroups = explode(',', $rule->getCustomerGroups());
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            } elseif ($backendModelSession->getId()) {
                $customerGroupId = $backendModelSession->getCustomerGroupId();
            } else {
                $customerGroupId = 0;
            }
            if (
                !$rule->getStatus()
                || !in_array($customerGroupId, $customerGroups, false)
                || !(in_array($quote->getStoreId(), $stores, false) || in_array('0', $stores, true))
            ) {
                continue;
            }
            $address = $this->getAddressToValidate($quote);
            if ($rule->validate($address)) {
                if ((int) $rule->getApplyType() === ApplyType::AUTOMATIC) {
                    if ($rule->getStopFurtherProcessing()) {
                        break;
                    }
                    continue;
                }

                $applyRule[$rule->getId()] = $rule->getData();
                if ($rule->getStopFurtherProcessing()) {
                    break;
                }
            }
        }

        return $applyRule;
    }

    /**
     * Assign extra fee amount and label to address object
     *
     * @param Quote $quote
     * @param Address\Total $total
     *
     * @return array
     * @throws Exception
     */
    public function fetch(Quote $quote, Total $total)
    {
        $fullActionName = $this->request->getFullActionName();
        if (!$this->helper->isEnabled() || in_array($fullActionName, [
            'multishipping_checkout_overview',
            'multishipping_checkout_overviewPost'
        ], true)) {
            $result             = [];
            $extraFee           = $this->helper->getMpExtraFee($total, 4);
            $extraFeeAmount     = 0;
            $baseExtraFeeAmount = 0;
            foreach ($extraFee as $ruleId => $option) {
                $extraFeeAmount     += $option['value'];
                $baseExtraFeeAmount += $option['base_value'];
                $option['title']    = $option['rule_label'] . (strpos($option['code'], 'auto') === false
                    ? ' - ' . $option['label'] : $option['label']);
                $result[]           = $option;
                $quote->setGrandTotal($quote->getOdGrandTotal() + $option['value_incl_tax']);
                $quote->setBaseGrandTotal($quote->getOdBaseGrandTotal() + $option['base_value_incl_tax']);
                $quote->setOdGrandTotal($quote->getOdGrandTotal() + $option['value_incl_tax']);
                $quote->setOdBaseGrandTotal($quote->getOdBaseGrandTotal() + $option['base_value_incl_tax']);
            }

            $total->setGrandTotal($total->getGrandTotal() + $extraFeeAmount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $baseExtraFeeAmount);

            $total->setOdGrandTotal($total->getOdGrandTotal() + $extraFeeAmount);
            $total->setOdBaseGrandTotal($total->getOdBaseGrandTotal() + $baseExtraFeeAmount);

            $addresses = $quote->getAllShippingAddresses();
            /** @var Address $address */
            foreach ($addresses as $address) {
                if ($address->getId() === $total->getAddressId()) {
                    $address->setGrandTotal($total->getGrandTotal());
                    $address->setBaseGrandTotal($total->getBaseGrandTotal());
                    $address->setOdGrandTotal($total->getOdGrandTotal());
                    $address->setOdBaseGrandTotal($total->getOdBaseGrandTotal());

                    $address->save();
                }
            }

            $quote->save();

            return $result;
        }

        $result   = $this->calculateAutoExtraFee($quote, true);
        $extraFee = $this->helper->getMpExtraFee($quote);

        if (empty($extraFee)) {
            $this->helper->setMpExtraFee($quote, $result, DisplayArea::TOTAL);

            return $result;
        }
        $applyRule = $this->getAllApplyRule($quote);
        foreach ($extraFee as $ruleId => $option) {
            if (!isset($applyRule[$ruleId])) {
                continue;
            }
            /** @var Rule $rule */
            $rule     = $this->ruleFactory->create()->load($ruleId);
            $options  = $rule->getOptions() ? Data::jsonDecode($rule->getOptions())['option']['value'] : [];
            $taxClass = $rule->getFeeTax();

            if (is_array($option)) {
                foreach ($option as $item) {
                    [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax] =
                        $this->calculateExtraFeeAmount($quote, $options[$item], $taxClass);
                    $result[] = [
                        'code'                => "mp_extra_fee_rule_{$ruleId}_{$item}",
                        'title'               => __($options[$item][$quote->getStoreId()] ?: $options[$item][0]),
                        'label'               => __($options[$item][$quote->getStoreId()] ?: $options[$item][0]),
                        'value'               => $ruleFeeAmount,
                        'base_value'          => $baseRuleFeeAmount,
                        'base_value_incl_tax' => $baseRuleFeeAmountInclTax,
                        'value_incl_tax'      => $ruleFeeAmountInclTax,
                        'value_excl_tax'      => $ruleFeeAmount,
                        'rf'                  => $rule->getRefundable(),
                        'display_area'        => $rule->getArea() ?: '3',
                        'apply_type'          => $rule->getApplyType(),
                        'rule_label'          => $this->helper->getRuleLabel($rule, $quote->getStoreId())
                    ];
                }
            } else {
                [$baseRuleFeeAmount, $ruleFeeAmount, $baseRuleFeeAmountInclTax, $ruleFeeAmountInclTax] =
                    $this->calculateExtraFeeAmount($quote, $options[$option], $taxClass);
                $result[] = [
                    'code'                => "mp_extra_fee_rule_{$ruleId}_{$option}",
                    'title'               => __($options[$option][$quote->getStoreId()] ?: $options[$option][0]),
                    'label'               => __($options[$option][$quote->getStoreId()] ?: $options[$option][0]),
                    'value'               => $ruleFeeAmount,
                    'base_value'          => $baseRuleFeeAmount,
                    'value_incl_tax'      => $ruleFeeAmountInclTax,
                    'value_excl_tax'      => $ruleFeeAmount,
                    'base_value_incl_tax' => $baseRuleFeeAmountInclTax,
                    'rf'                  => $rule->getRefundable(),
                    'display_area'        => $rule->getArea() ?: '3',
                    'apply_type'          => $rule->getApplyType(),
                    'rule_label'          => $this->helper->getRuleLabel($rule, $quote->getStoreId())
                ];
            }
        }
        $this->helper->setMpExtraFee($quote, $result, DisplayArea::TOTAL);
        $quote->save();

        return $result;
    }
}
