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

use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session as CheckoutSession;
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
use Magento\Customer\Model\Session;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ExtraFee\Model\Config\Source\DisplayArea;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule\CollectionFactory;
use Mageplaza\ExtraFee\Model\Rule;

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
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param CollectionFactory $ruleCollectionFactory
     * @param CheckoutCart $cart
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        CollectionFactory $ruleCollectionFactory,
        CheckoutCart $cart,
        QuoteFactory $quoteFactory
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->quoteRepository       = $quoteRepository;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->cart                  = $cart;
        $this->quoteFactory          = $quoteFactory;

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
            foreach ($object->getItems() as $item) {
                if (($orderItems->getItemId() === $item->getOrderItemId()) && $orderItems->getProductType() != 'configurable') {
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
        foreach ($extraFeeTotals as $extraFeeTotal) {
            $extraFeeAmount           += $extraFeeTotal['value'];
            $baseExtraFeeAmount       += $extraFeeTotal['base_value'];
            $valueEachItem            = $extraFeeTotal['value'] / $order->getTotalQtyOrdered();
            $baseValueEachItem        = $extraFeeTotal['base_value'] / $order->getTotalQtyOrdered();
            $baseValueInclTaxEachItem = $extraFeeTotal['base_value_incl_tax'] / $order->getTotalQtyOrdered();
            $valueExclTaxEachItem     = $extraFeeTotal['value_excl_tax'] / $order->getTotalQtyOrdered();
            $valueInclTaxEachItem     = $extraFeeTotal['value_incl_tax'] / $order->getTotalQtyOrdered();

            foreach ($order->getItems() as $item) {
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
            $order->setOdGrandTotal($order->getOdGrandTotal() + $extraFeeAmount);
            $order->setOdBaseGrandTotal($order->getOdBaseGrandTotal() + $baseExtraFeeAmount);
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
                    if (in_array($index, ['totals', 'is_invoiced', 'is_refunded'])) {
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
     * Reset cart quote
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resetQuote()
    {
        $currentQuote = $this->checkoutSession->getQuote();
        if (!$currentQuote->getId()) {
            $currentQuote = $this->quoteFactory->create();
            $customer     = $this->objectManager->create(Session::class)->getCustomer();
            if ($customer->getId()) {
                $currentQuote->setCustomerId($customer->getId());
            }
        }
        $this->cart->setQuote($currentQuote);
    }

    public function checkProductSpecial($quote)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

        /** compare  cart or payment page: route api */
        $request = $objectManager->create('Magento\Framework\App\Request\Http');
        if ($request->getRequestUri() == "/checkout/cart/") {
            return false;
        } else {

            /** remove with payment rule */
            $mpExtraFee = json_decode($quote->getMpExtraFee(), true);
            if (count($mpExtraFee['totals']) == 0) {
                return false;
            }

            $listPaymentReturn = ['ondemand_qrcodepayment', 'ondemand_billpayment'];
            if (!empty($quote->getPayment()->getMethod()) && in_array($quote->getPayment()->getMethod(), $listPaymentReturn)) {
                return false;
            }

            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                return false;
            }
            $routeReferer = $_SERVER['HTTP_REFERER'];
            $routeCheckoutCart =  $_SERVER['HTTP_ORIGIN'] . '/checkout/cart/';
            if ($routeReferer == $routeCheckoutCart) {
                return false;
            }
        }

        $listCategory = $quote->getItems()[0]->getProduct()->getCategoryIds();
        $categoryByUrlKey = $categoryFactory->create()
            ->addAttributeToFilter('url_key', 'idbook')
            ->addAttributeToSelect('*');
        $isInCategory = in_array($categoryByUrlKey->getFirstItem()->getId(), $listCategory);

        if (!empty($categoryByUrlKey->getFirstItem()->getId()) && $isInCategory) {
            return true;
        }

        return false;
    }
}
