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

namespace Mageplaza\ExtraFee\Helper;

use DateTime;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayArea;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule\CollectionFactory;
use Mageplaza\ExtraFee\Model\Rule;
use Mageplaza\ExtraFee\Model\RuleFactory;

/**
 * Class Data
 * @package Mageplaza\ExtraFee\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH = 'mp_extra_fee';

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param CollectionFactory $ruleCollectionFactory
     * @param CheckoutCart $cart
     * @param CustomerSession $customerSession
     * @param QuoteFactory $quoteFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        CollectionFactory $ruleCollectionFactory,
        CheckoutCart $cart,
        CustomerSession $customerSession,
        QuoteFactory $quoteFactory,
        RuleFactory $ruleFactory
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->quoteRepository       = $quoteRepository;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->cart                  = $cart;
        $this->quoteFactory          = $quoteFactory;
        $this->customerSession       = $customerSession;
        $this->ruleFactory           = $ruleFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null|QuoteModel $quote
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function collectTotals($quote = null)
    {
        if ($this->isAdmin()) {
            return $this;
        }

        if ($quote === null) {
            /** @var QuoteModel $quote */
            $quote = $this->getCheckoutSession()->getQuote();
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);

        $this->quoteRepository->save($quote->collectTotals());

        return $this;
    }

    /**
     * Get checkout session for admin and frontend
     *
     * @return CheckoutSession|mixed
     */
    public function getCheckoutSession()
    {
        if (!$this->checkoutSession) {
            $this->checkoutSession = $this->objectManager
                ->get($this->isAdmin() ? Quote::class : CheckoutSession::class);
        }

        return $this->checkoutSession;
    }

    /**
     * @param QuoteModel|Order|Address|Object $quote
     *
     * @return array|mixed
     */
    public function getExtraFeeTotals($quote)
    {
        $extraFee = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];

        return isset($extraFee['totals']) ? $extraFee['totals'] : [];
    }

    /**
     * @param QuoteModel|Order|Address $quote
     * @param array|string $value
     * @param string $area
     */
    public function setMpExtraFee($quote, $value, $area)
    {
        $extraFee = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];
        if (is_string($value)) {
            $this->setNoteToSession($value);
        }

        $checkoutSession = $this->getCheckoutSession();

        if (!isset($extraFee['summary'])) {
            $ruleCollection = $this->ruleCollectionFactory->create()->addFieldToFilter('area', '3');
            $defaults       = [];
            foreach ($ruleCollection as $rule) {
                $default = self::jsonDecode($rule->getOptions())['default'];
                if ($default) {
                    $defaults[$rule->getId()] = $default[0];
                }
            }
            $extraFee['summary'] = http_build_query(['rule' => $defaults]);
        }

        $extraFeeNote = $checkoutSession->getExtraFeeNote() ?: [];

        if (count($extraFeeNote) && is_string($value)) {
            $value = $this->setNoteToParams($extraFeeNote, $value);
        }

        switch ((int) $area) {
            case DisplayArea::PAYMENT_METHOD:
                $extraFee['payment'] = $value;
                break;
            case DisplayArea::SHIPPING_METHOD:
                $extraFee['shipping'] = $value;
                break;
            case DisplayArea::CART_SUMMARY:
                $extraFee['summary'] = $value;
                break;
            case DisplayArea::TOTAL:
                $extraFee['totals'] = $value;
        }

        $quote->setMpExtraFee($this::jsonEncode($extraFee))->save();
    }

    /**
     * @param Rule $rule
     * @param int $storeId
     *
     * @return string
     */
    public function getRuleLabel($rule, $storeId)
    {
        $labels = $rule->getlabels() ? $this::jsonDecode($rule->getlabels()) : [];

        return isset($labels[$storeId]) ? ($labels[$storeId] ?: $rule->getName()) : '';
    }

    /**
     * @param QuoteModel|Order $quote
     * @param int $invoiceId
     */
    public function setInvoiced($quote, $invoiceId)
    {
        $extraFee                = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];
        $extraFee['is_invoiced'] = $invoiceId;
        $quote->setMpExtraFee($this::jsonEncode($extraFee))->save();
    }

    /**
     * @param QuoteModel|Order|Object $quote
     *
     * @return bool|mixed
     */
    public function isInvoiced($quote)
    {
        $extraFee = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];

        return isset($extraFee['is_invoiced']) ? $extraFee['is_invoiced'] : false;
    }

    /**
     * @param Quote|Order $quote
     * @param int $creditmemoId
     */
    public function setRefunded($quote, $creditmemoId)
    {
        $extraFee                = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];
        $extraFee['is_refunded'] = $creditmemoId;
        $quote->setMpExtraFee($this::jsonEncode($extraFee))->save();
    }

    /**
     * @param QuoteModel|Order|Object $quote
     *
     * @return bool|mixed
     */
    public function isRefunded($quote)
    {
        $extraFee = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];

        return isset($extraFee['is_refunded']) ? $extraFee['is_refunded'] : false;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isOscPage()
    {
        $moduleEnable = $this->isModuleOutputEnabled('Mageplaza_Osc');
        $isOscModule  = ($this->_request->getRouteName() === 'onestepcheckout');

        return $moduleEnable && $isOscModule && $this->isEnabled();
    }

    /**
     * @return mixed
     */
    public function getDefaultCountryId()
    {
        return $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|null
     */
    public function getDefaultRegionId()
    {
        $defaultRegionId = $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_DEFAULT_REGION,
            ScopeInterface::SCOPE_STORE
        );

        if (0 == $defaultRegionId) {
            $defaultRegionId = null;
        }

        return $defaultRegionId;
    }

    /**
     * @return mixed
     */
    public function getDefaultPostcode()
    {
        return $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Object $object
     * @param Order $order
     *
     * @return array
     */
    public function getObjectExtraFeeTotals($object, $order)
    {
        $extraFeeTotals = [];
        $extraFeeItems  = [];
        foreach ($order->getItems() as $orderItems) {
            if ($orderItems->getProductType() == 'configurable') {
                continue;
            }
            foreach ($object->getItems() as $item) {
                if ($orderItems->getItemId() === $item->getOrderItemId()) {
                    $mpExtraFees = Data::jsonDecode($orderItems->getMpExtraFee());
                    $item->setMpExtraFee($orderItems->getMpExtraFee());
                    foreach ($mpExtraFees as $mpExtraFee) {
                        $mpExtraFee['qty'] = $item->getQty();
                        $extraFeeItems[]   = $mpExtraFee;
                    }
                }
            }
        }

        foreach ($extraFeeItems as $extraFeeItem) {
            if (!isset($extraFeeTotals[$extraFeeItem['code']])) {
                $extraFeeTotals[$extraFeeItem['code']] = [
                    'code'                => $extraFeeItem['code'],
                    'title'               => $extraFeeItem['title'],
                    'label'               => $extraFeeItem['label'],
                    'value'               => $extraFeeItem['value'] * $extraFeeItem['qty'],
                    'base_value'          => $extraFeeItem['base_value'] * $extraFeeItem['qty'],
                    'value_incl_tax'      => $extraFeeItem['value_incl_tax'] * $extraFeeItem['qty'],
                    'value_excl_tax'      => $extraFeeItem['value_excl_tax'] * $extraFeeItem['qty'],
                    'base_value_incl_tax' => $extraFeeItem['base_value_incl_tax'] * $extraFeeItem['qty'],
                    'rf'                  => $extraFeeItem['rf'],
                    'display_area'        => $extraFeeItem['display_area'],
                    'apply_type'          => $extraFeeItem['apply_type'],
                    'rule_label'          => $extraFeeItem['rule_label']
                ];
            } else {
                $extraFeeTotals[$extraFeeItem['code']]['value']               += $extraFeeItem['value']
                    * $extraFeeItem['qty'];
                $extraFeeTotals[$extraFeeItem['code']]['base_value']          += $extraFeeItem['base_value']
                    * $extraFeeItem['qty'];
                $extraFeeTotals[$extraFeeItem['code']]['value_incl_tax']      += $extraFeeItem['value_incl_tax']
                    * $extraFeeItem['qty'];
                $extraFeeTotals[$extraFeeItem['code']]['value_excl_tax']      += $extraFeeItem['value_excl_tax']
                    * $extraFeeItem['qty'];
                $extraFeeTotals[$extraFeeItem['code']]['base_value_incl_tax'] += $extraFeeItem['base_value_incl_tax']
                    * $extraFeeItem['qty'];
            }
        }

        return $extraFeeTotals;
    }

    /**
     * @param Order $order
     * @param Quote|Address $quote
     */
    public function setExtraFeeForItems($order, $quote)
    {
        $order->setMpExtraFee($quote->getMpExtraFee());

        $billingExtraFee  = $this->getMpExtraFee($order, DisplayArea::PAYMENT_METHOD);
        $shippingExtraFee = $this->getMpExtraFee($order, DisplayArea::SHIPPING_METHOD);
        $extraFee         = $this->getMpExtraFee($order, DisplayArea::CART_SUMMARY);

        if (!empty($billingExtraFee)) {
            $order->setHasBillingExtraFee(true);
        }
        if (!empty($shippingExtraFee)) {
            $order->setHasShippingExtraFee(true);
        }
        if (!empty($extraFee)) {
            $order->setHasExtraFee(true);
        }

        $totalsFees         = [];
        $extraFeeAmount     = 0;
        $baseExtraFeeAmount = 0;
        $extraFeeTotals     = $this->getMpExtraFee($order, DisplayArea::TOTAL);
        $extraFee           = $this->getMpExtraFee($quote);
        foreach ($extraFeeTotals as $extraFeeTotal) {
            $extraFeeAmount           += $extraFeeTotal['value'];
            $baseExtraFeeAmount       += $extraFeeTotal['base_value'];
            $valueEachItem            = $extraFeeTotal['value'] / $order->getTotalQtyOrdered();
            $baseValueEachItem        = $extraFeeTotal['base_value'] / $order->getTotalQtyOrdered();
            $baseValueInclTaxEachItem = $extraFeeTotal['base_value_incl_tax'] / $order->getTotalQtyOrdered();
            $valueExclTaxEachItem     = $extraFeeTotal['value_excl_tax'] / $order->getTotalQtyOrdered();
            $valueInclTaxEachItem     = $extraFeeTotal['value_incl_tax'] / $order->getTotalQtyOrdered();

            $ruleId = explode('_', $extraFeeTotal['code'])[4];
            foreach ($order->getItems() as $item) {
                foreach ($extraFee as $Id => $option) {
                    if ($ruleId != $Id) {
                        continue;
                    }
                    if (is_array($option)) {
                        foreach ($option as $op) {
                            /** @var Rule $rule */
                            $rule     = $this->ruleFactory->create()->load($ruleId);
                            $options  = $rule->getOptions() ? Data::jsonDecode($rule->getOptions())['option']['value'] : [];
                            if ((int)$options[$op]['type'] === 3) {
                                $valueEachItem            = $item->getOriginalPrice() * $options[$op]['amount'] / 100;
                                $baseValueEachItem        = $item->getBaseOriginalPrice() * $options[$op]['amount'] / 100;
                                $baseValueInclTaxEachItem = $item->getBasePriceInclTax() * $options[$op]['amount'] / 100;
                                $valueExclTaxEachItem     = $item->getOriginalPrice() * $options[$op]['amount'] / 100;
                                $valueInclTaxEachItem     = $item->getPriceInclTax() * $options[$op]['amount'] / 100;
                            }
                        }
                    }
                }
                $totalsFees[$item->getProductId()][] = [
                    'code'                => $extraFeeTotal['code'],
                    'title'               => $extraFeeTotal['title'],
                    'label'               => $extraFeeTotal['label'],
                    'value'               => $valueEachItem,
                    'base_value'          => $baseValueEachItem,
                    'value_incl_tax'      => $baseValueInclTaxEachItem,
                    'value_excl_tax'      => $valueExclTaxEachItem,
                    'base_value_incl_tax' => $valueInclTaxEachItem,
                    'rf'                  => $extraFeeTotal['rf'],
                    'display_area'        => $extraFeeTotal['display_area'],
                    'apply_type'          => $extraFeeTotal['apply_type'],
                    'rule_label'          => $extraFeeTotal['rule_label']
                ];
            }
        }

        if ($quote instanceof Address) {
            $order->setGrandTotal($order->getGrandTotal() + $extraFeeAmount);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $baseExtraFeeAmount);
        }

        if (!empty($totalsFees)) {
            foreach ($order->getItems() as $item) {
                if (isset($totalsFees[$item->getProductId()])) {
                    $item->setMpExtraFee(Data::jsonEncode($totalsFees[$item->getProductId()]));
                }
            }
        }
    }

    /**
     * @return \Mageplaza\ExtraFee\Model\ResourceModel\Rule\Collection
     */
    public function getRuleCollection()
    {
        $currentTime = new DateTime();

        return $this->ruleCollectionFactory->create()
            ->addFieldToFilter('from_date', [
                ['lteq' => $currentTime->format('Y-m-d H:i:s')],
                ['null' => true]
            ])
            ->addFieldToFilter('to_date', [
                ['gteq' => $currentTime->format('Y-m-d H:i:s')],
                ['null' => true]
            ])
            ->setOrder('priority', 'ASC');
    }

    /**
     * @param $collection
     * @param $ruleId
     * @param $type
     *
     * @return int|mixed
     */
    public function getReportTotal($collection, $ruleId, $type)
    {
        $totals = 0;
        foreach ($collection as $element) {
            foreach ($element->getItems() as $item) {
                if ($item->getMpExtraFee()) {
                    $extraFee = $this->unserialize($item->getMpExtraFee());
                    if (count($extraFee) == 0) {
                        continue;
                    }
                    foreach ($extraFee as $total) {
                        $id = array_filter(preg_split("/\D+/", $total['code']));
                        $id = reset($id);
                        if ($id == $ruleId) {
                            if ($type == 'invoice') {
                                $totals += $total['base_value'] * $item->getQty();
                            }
                            if ($type == 'creditmemo' && $total['rf'] == 1) {
                                $totals += $total['base_value'] * $item->getQty();
                            }
                        }
                    }
                }
            }
        }

        return $totals;
    }

    /**
     * @param $quote
     */
    public function setExtraFeeNote($quote)
    {
        $extraFee     = $this::jsonDecode($quote->getMpExtraFee());
        $extraFeeNote = $this->checkoutSession->getExtraFeeMultiNote() ?: [];

        if (count($extraFeeNote) && $quote->getAddressId()) {
            $addressId = $quote->getAddressId();
            if (isset($extraFeeNote[$addressId])) {
                $extraFee['note'] = $extraFeeNote[$addressId];
                $quote->setMpExtraFee($this::jsonEncode($extraFee));
                $quote->save();
                unset($extraFeeNote[$addressId]);
            }
        }

        if (!count($extraFeeNote)) {
            $this->checkoutSession->unsExtraFeeMultiNote();
        }
    }

    /**
     * @param QuoteModel|Order|Address|Address\Total $quote
     * @param bool $area
     *
     * @return array
     */
    public function getMpExtraFee($quote, $area = false)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $extraFee = $quote->getMpExtraFee() ? $this::jsonDecode($quote->getMpExtraFee()) : [];
        switch ($area) {
            case DisplayArea::PAYMENT_METHOD:
                if (isset($extraFee['payment'])) {
                    parse_str($extraFee['payment'], $result);

                    return $result;
                }

                return [];
            case DisplayArea::SHIPPING_METHOD:
                if (isset($extraFee['shipping'])) {
                    parse_str($extraFee['shipping'], $result);

                    return $result;
                }

                return [];
            case DisplayArea::CART_SUMMARY:
                if (isset($extraFee['summary'])) {
                    parse_str($extraFee['summary'], $result);

                    return $result;
                }

                return [];
            case DisplayArea::TOTAL:
                if (isset($extraFee['totals'])) {
                    return $extraFee['totals'];
                }

                return [];
            default:
                $result = [];
                foreach ($extraFee as $index => $item) {
                    if (in_array($index, ['totals', 'is_invoiced', 'is_refunded', 'note'])) {
                        continue;
                    }
                    parse_str($item, $rule);
                    if (isset($rule['rule']) && is_array($rule['rule'])) {
                        foreach ($rule['rule'] as $key => $value) {
                            $result[$key] = $value;
                        }
                    }
                }

                return $result;
        }
    }

    /**
     * @param Rule $rule
     *
     * @return false|int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkCustomerGroup($rule)
    {
        $customerGroups  = explode(',', $rule->getCustomerGroups());
        $customerSession = $this->customerSession->create();
        $customerGroupId = $customerSession->isLoggedIn() ? $customerSession->getCustomerGroupId() : 0;

        if (in_array($customerGroupId, $customerGroups)) {
            return true;
        }

        return false;
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkStoreIds($rule)
    {
        $storeIds     = explode(',', $rule->getStoreIds());
        $currentStore = $this->storeManager->getStore()->getId();
        if (in_array(0, $storeIds)) {
            return true;
        } else {
            if (in_array($currentStore, $storeIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $moduleName
     *
     * @return bool
     */
    public function moduleIsEnable($moduleName)
    {
        if ($this->_moduleManager->isEnabled($moduleName)) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     */
    protected function setNoteToSession($data)
    {
        $checkoutSession = $this->getCheckoutSession();
        $extraFeeNote    = $checkoutSession->getExtraFeeNote() ?: [];
        $params          = [];
        parse_str($data, $params);

        foreach ($params as $key => $value) {
            if (str_contains($key, 'mp-extrafee-note') && !empty($value)) {
                $extraFeeNote[$key] = $value;
            }
        }

        $checkoutSession->setExtraFeeNote($extraFeeNote);
    }

    /**
     * @param $extraFeeNote
     * @param $data
     *
     * @return string
     */
    protected function setNoteToParams($extraFeeNote, $data)
    {
        $data = explode('&', $data);

        foreach ($extraFeeNote as $key => $value) {
            foreach ($data as $index => $param) {
                if (str_contains($param, $key)) {
                    $data[$index] = $key . '=' . $value;
                }
            }
        }
        $data = implode('&', $data);

        return $data;
    }
}
