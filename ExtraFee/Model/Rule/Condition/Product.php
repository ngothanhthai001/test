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

namespace Mageplaza\ExtraFee\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogRule\Model\Rule\Condition\Product as CatalogRuleProduct;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\QuoteFactory;
use Magento\Rule\Model\Condition\Context;
use Magento\Catalog\Model\ProductCategoryList;

/**
 * Class Product
 * @package Mageplaza\ExtraFee\Model\Rule\Condition
 */
class Product extends CatalogRuleProduct
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Data $backendData
     * @param Config $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResource $productResource
     * @param Collection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param QuoteFactory $quoteFactory
     * @param array $data
     * @param ProductCategoryList|null $categoryList
     */
    public function __construct(
        Context $context,
        Data $backendData,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        QuoteFactory $quoteFactory,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->quoteFactory = $quoteFactory;

        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function validate(AbstractModel $model)
    {
        $quoteId = $model->getQuoteId();
        if ($quoteId) {
            $quote = $this->quoteFactory->create()->load($quoteId);
        } else {
            $quote = $model;
        }

        $allItems = $quote->getAllItems();

        foreach ($allItems as $item) {
            $storeId        = $item->getStoreId();
            $currentProduct = $this->productRepository->getById($item->getProductId(), false, $storeId);
            $data           = $currentProduct->getData();

            $data['activity'] = $currentProduct->getActivity() ? explode(',', $currentProduct->getActivity()) : [];
            $model->addData($data);
            if (parent::validate($model)) {
                return true;
            }
        }

        return false;
    }
}
