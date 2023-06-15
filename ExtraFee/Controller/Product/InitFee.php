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

namespace Mageplaza\ExtraFee\Controller\Product;

use DateTime;
use Exception;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ExtraFee\Helper\Data;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule\CollectionFactory;
use Mageplaza\ExtraFee\Model\Rule;
use Mageplaza\ExtraFee\Model\Total\Quote\ExtraFee as CalculateExtraFee;

/**
 * Class InitFee
 * @package Mageplaza\ExtraFee\Controller\Product
 */
class InitFee extends Action
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollection;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var CalculateExtraFee
     */
    protected $extraFeeCalculate;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * InitFee constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * @param CollectionFactory $ruleCollection
     * @param PricingHelper $pricingHelper
     * @param CalculateExtraFee $extraFeeCalculate
     * @param QuoteFactory $quoteFactory
     * @param Data $helperData
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        CollectionFactory $ruleCollection,
        PricingHelper $pricingHelper,
        CalculateExtraFee $extraFeeCalculate,
        QuoteFactory $quoteFactory,
        Data $helperData,
        Cart $cart
    ) {
        parent::__construct($context);

        $this->cart              = $cart;
        $this->storeManager      = $storeManager;
        $this->productRepository = $productRepository;
        $this->ruleCollection    = $ruleCollection;
        $this->pricingHelper     = $pricingHelper;
        $this->extraFeeCalculate = $extraFeeCalculate;
        $this->helperData        = $helperData;
        $this->quoteFactory      = $quoteFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result    = $this->resultFactory->create('json');
        $productId = $this->_request->getParam('product');
        $storeId   = $this->storeManager->getStore()->getId();
        $product   = $this->productRepository->getById($productId, false, $storeId);

        $extraFeeData = $this->getExtraFee($product);

        if (!$extraFeeData) {
            $extraFeeContent = '<p>' . __('There are no extra fee can be applied') . '</p>';
            $result->setData(['content' => $extraFeeContent]);
        } else {
            $extraFeeContent = $this->getExtraFeeContent($extraFeeData);
            $result->setData(['content' => $extraFeeContent]);
        }

        return $result;
    }

    /**
     * Get Extra fee data
     *
     * @param $product
     *
     * @return array|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getExtraFee($product)
    {
        $productType  = $product->getTypeId();
        $extraFeeData = [];

        switch ($productType) {
            case 'configurable':
                $params      = [];
                $children    = $product->getTypeInstance()->getUsedProducts($product);
                $addChildren = $this->getAddChildren($children);

                foreach ($addChildren as $child) {
                    $params['product']       = $product->getId();
                    $params['price']         = $child->getFinalPrice();
                    $params['qty']           = 1;
                    $options                 = [];
                    $productAttributeOptions = $product->getTypeInstance(true)
                        ->getConfigurableAttributesAsArray($product);
                    foreach ($productAttributeOptions as $option) {
                        $options[$option['attribute_id']] = $child->getData($option['attribute_code']);
                    }
                    $params['super_attribute'] = $options;
                    $extraFeeData              = $this->addProduct($product, $params, $extraFeeData);
                }
                break;
            case 'grouped':
                $children          = $product->getTypeInstance(true)->getAssociatedProducts($product);
                $addChildren       = $this->getAddChildren($children);
                $params            = [];
                $params['product'] = $product->getId();
                $params['item']    = $product->getId();
                foreach ($addChildren as $child) {
                    $params['super_group']                  = $this->_request->getParam('super_group');
                    $params['super_group'][$child->getId()] = "1";
                    $params['price']                        = $child->getFinalPrice();
                    $extraFeeData                           = $this->addProduct($product, $params, $extraFeeData);
                }
                break;
            case 'bundle':
                $params = [
                    'product'       => $product->getId(),
                    'item'          => $product->getId(),
                    'qty'           => '1',
                    'bundle_option' => $this->getBundleOption($product)
                ];

                $minPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                $maxPrice = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();

                // add bundle with min option
                $params['price'] = $minPrice;
                $extraFeeData    = $this->addProduct($product, $params, $extraFeeData);

                // add bundle with max option
                $params['price'] = $maxPrice;
                $extraFeeData    = $this->addProduct($product, $params, $extraFeeData);

                break;
            default:
                $params       = [
                    'qty'   => '1',
                    'price' => $product->getFinalPrice()
                ];
                $extraFeeData = $this->addProduct($product, $params, $extraFeeData);
                break;
        }

        return $extraFeeData;
    }

    /**
     * @param $product
     * @param $params
     * @param $extraFeeData
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function addProduct($product, $params, $extraFeeData)
    {
        $currentTime    = new DateTime();
        $storeId        = $this->storeManager->getStore()->getId();
        $ruleCollection = $this->ruleCollection->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('from_date', [
                ['lteq' => $currentTime->format('Y-m-d H:i:s')],
                ['null' => true]
            ])
            ->addFieldToFilter('to_date', [
                ['gteq' => $currentTime->format('Y-m-d H:i:s')],
                ['null' => true]
            ])
            ->setOrder('priority', 'asc');

        $quoteId = $this->cart->getQuote()->getId();

        try {
            $request   = new DataObject($params);
            $quoteCart = $this->quoteFactory->create()->load($quoteId);
            $totalQty  = $quoteCart->getItemsQty() ?: 0;
            $quoteCart->addProduct($product, $request);

            $baseSubTotal = $quoteCart->getBaseSubtotal();
            $quoteCart->setItemsQty($totalQty + $params['qty']);

            if (!$quoteCart->getBaseSubtotal() && !$quoteCart->getId()) {
                $quoteCart->setBaseSubtotal($params['price']);
                $quoteCart->setBaseSubtotalWithDiscount($params['price']);
            } elseif ($quoteCart->getId()) {
                $quoteCart->setBaseSubtotal($baseSubTotal + $params['price']);
                $quoteCart->setBaseSubtotalWithDiscount($quoteCart->getBaseSubtotalWithDiscount() + $params['price']);
            }

            foreach ($ruleCollection as $rule) {
                if (!$this->helperData->checkCustomerGroup($rule) || !$this->helperData->checkStoreIds($rule)) {
                    continue;
                }
                if ($rule->validate($quoteCart)) {
                    $ruleData  = $this->getRuleData($rule, $quoteCart);
                    $labels    = $rule->getlabels() ? Data::jsonDecode($rule->getlabels()) : [];
                    $ruleLabel = isset($labels[$storeId]) ? ($labels[$storeId] ?: $rule->getName()) : '';

                    $ruleId                               = $rule->getId();
                    $extraFeeData[$ruleId]['description'] = $rule->getDescription();
                    $extraFeeData[$ruleId]['name']        = $ruleLabel;

                    $data = array_key_exists('data', $extraFeeData[$ruleId]) ? $extraFeeData[$ruleId]['data'] : [];
                    if (!array_key_exists($ruleLabel, $data)) {
                        $data[$ruleLabel] = [];
                    }
                    foreach ($ruleData as $key => $value) {
                        $valueType = array_first(explode(',', $value));
                        $valueData = array_last(explode(',', $value));
                        if (!array_key_exists($key, $data[$ruleLabel])) {
                            $data[$ruleLabel][$key] = $key . ': ' . $valueData;
                        } elseif ($valueType == '3') {
                            $data[$ruleLabel][$key] .= ' - ' . $valueData;
                        }
                    }
                    $extraFeeData[$ruleId]['data'] = $data;
                }
            }
        } catch (Exception $e) {
            return $extraFeeData;
        }

        return $extraFeeData;
    }

    /**
     * @param $rule
     * @param $quote
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getRuleData($rule, $quote)
    {
        /** @var Rule $rule */
        $taxClass = $rule->getFeeTax();
        $storeId  = $this->storeManager->getStore()->getId();
        $data     = [];

        if ($rule->getApplyType() == 1) {
            $labels        = $rule->getlabels() ? Data::jsonDecode($rule->getlabels()) : [];
            $ruleLabel     = isset($labels[$storeId]) ? ($labels[$storeId] ?: $rule->getName()) : '';
            $calculatedFee = $this->extraFeeCalculate->calculateExtraFeeAmount($quote, $rule, $taxClass);
            $ruleFeeAmount = $this->pricingHelper->currencyByStore($calculatedFee[0], $storeId, true, false);

            $data[$ruleLabel] = $rule->getFeeType() . ',' . $ruleFeeAmount;
        } else {
            $options = $rule->getOptions() ? Data::jsonDecode($rule->getOptions())['option']['value'] : [];
            foreach ($options as $option) {
                $ruleLabel        = isset($option[$storeId]) ? ($option[$storeId] ?: $option[0]) : '';
                $calculatedFee    = $this->extraFeeCalculate->calculateExtraFeeAmount($quote, $option, $taxClass);
                $ruleFeeAmount    = $this->pricingHelper->currencyByStore($calculatedFee[0], $storeId, true, false);
                $data[$ruleLabel] = $option['type'] . ',' . $ruleFeeAmount;
            }
        }

        return $data;
    }

    /**
     * @param $ruleData
     *
     * @return string
     */
    protected function getExtraFeeContent($ruleData)
    {
        $html = '';
        foreach ($ruleData as $key => $data) {
            $html .= '<div class="mp-extrafee-name">' . $data['name'] . '</div>';
            $html .= '<div class="mp-extrafee-desc">' . $data['description'] . '</div>';
            $html .= '<div class="content"><ul>';

            foreach ($data['data'] as $label => $option) {
                foreach ($option as $opt) {
                    $html .= '<li>' . $opt . '</li>';
                }
            }
            $html .= '</ul></div>';
        }

        return $html;
    }

    /**
     * @param $children
     *
     * @return array
     */
    protected function getAddChildren($children)
    {
        $minChild = $children[0];
        $maxChild = $children[0];

        foreach ($children as $child) {
            $finalPrice = $child->getFinalPrice();
            if ($finalPrice < $minChild->getFinalPrice()) {
                $minChild = $child;
            }
            if ($finalPrice > $maxChild->getFinalPrice()) {
                $maxChild = $child;
            }
        }

        return [$minChild, $maxChild];
    }

    /**
     * @param $product
     *
     * @return array
     */
    protected function getBundleOption($product)
    {
        /** @var Collection $selectionCollection */
        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );

        $bundleOptions = [];

        foreach ($selectionCollection as $selection) {
            $optionId                 = $selection->getOptionId();
            $bundleOptions[$optionId] = $selection->getSelectionId();
        }

        return $bundleOptions;
    }
}
