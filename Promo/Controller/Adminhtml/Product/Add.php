<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Controller\Adminhtml\Product;

use Amasty\Promo\Helper\Cart;
use Amasty\Promo\Model\PromoItemRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;

/**
 * @todo copy of \Amasty\Promo\Controller\Cart\Add, refactor and need to move business logic and cover by unit tests
 */
class Add extends Action implements HttpPostActionInterface
{
    private const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';

    /**
     * @var Cart
     */
    private $promoCartHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Quote
     */
    private $backendQuote;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Request whitelist parameters
     * @var array
     */
    private $requestOptions = [
        'super_attribute',
        'options',
        'super_attribute',
        'links',
        'giftcard_sender_name',
        'giftcard_sender_email',
        'giftcard_recipient_name',
        'giftcard_recipient_email',
        'giftcard_message',
        'giftcard_amount',
        'custom_giftcard_amount',
        'bundle_option',
        'bundle_option_qty',
    ];

    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        Context $context,
        Cart $promoCartHelper,
        ProductRepositoryInterface $productRepository,
        Quote $backendQuote,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        PromoItemRepository $promoItemRepository
    ) {
        parent::__construct($context);
        $this->promoCartHelper = $promoCartHelper;
        $this->productRepository = $productRepository;
        $this->backendQuote = $backendQuote;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->promoItemRepository = $promoItemRepository;
    }

    public function execute()
    {
        $data = $this->getItemsRequest();
        $updateTotalQty = false;
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $quote = $this->backendQuote->getQuote();

        try {
            $productQtyParams = [];

            foreach ($data as $params) {
                if (empty($params)) {
                    continue;
                }

                $productId = (int)$params['product_id'];

                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($productId);
                $sku = $product->getSku();
                /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemData $promoDataItem */
                $promoDataItem = $this->getPromoDataItem($sku, $params, (int)$quote->getId());
                if ($promoDataItem) {
                    $qty = $this->getQtyToAdd($promoDataItem, $params, $productId);
                    if (!empty($params['qty']) && ($qty >= $params['qty'])) {
                        $qty = $params['qty'];
                    }
                    $updateTotalQty = true;
                    $requestOptions = array_intersect_key($params, array_flip($this->requestOptions));

                    $this->promoCartHelper->addProduct(
                        $product,
                        $qty,
                        $promoDataItem,
                        $requestOptions,
                        $quote
                    );

                    $item = $quote->getItemByProduct($product);
                    $productQtyParams[$item->getItemId()] = $qty;
                }
            }
        } catch (LocalizedException $e) {
            $result = [
                'success' => false,
                'error' => __('Free gift couldn\'t be added to the cart.', $e->getMessage())
            ];

            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => __('Something went wrong.')
            ];
            $this->logger->error($e->getMessage());

            return $resultJson->setData($result);
        }

        if ($updateTotalQty) {
            $this->quoteRepository->save($quote);
            $this->backendQuote->setPromoUpdate($productQtyParams);
            $result = ['success' => true];
        } else {
            $result = [
                'success' => false,
                'error' => __(
                    'Free gift couldn\'t be added to the cart.' .
                    'Please try again or contact the administrator for more information.'
                )
            ];
        }

        return $resultJson->setData($result);
    }

    /**
     * @return array
     */
    private function getItemsRequest()
    {
        if (!($data = $this->getRequest()->getParam('data', false))) {
            $data[] = $this->getRequest()->getParams();
        }

        return $data;
    }

    /**
     * @param string $sku
     * @param array $params
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemData|null
     * @since 2.5.0 promo item data is filtering by rule_id and sku instead only by sku
     * @since 2.14.0 promo item data can be obtained by quoteId
     */
    private function getPromoDataItem($sku, $params, int $quoteId)
    {
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId($quoteId);
        if (isset($params['rule_id']) && $ruleId = (int)$params['rule_id']) {
            $promoItemData = $promoItemsGroup->getItemBySkuAndRuleId($sku, $ruleId);
            if ($promoItemData && $promoItemData->getQtyToProcess() > 0) {
                return $promoItemData;
            }
        } else {
            $promoItemsData = $promoItemsGroup->getItemsBySku($sku);
            foreach ($promoItemsData as $promoItemData) {
                if ($promoItemData->getQtyToProcess() > 0) {
                    return $promoItemData;
                }
            }
        }

        return null;
    }

    /**
     * @param \Amasty\Promo\Model\ItemRegistry\PromoItemData $promoDataItem
     * @param array $params
     * @param int $productId
     *
     * @return float
     */
    private function getQtyToAdd($promoDataItem, $params, $productId)
    {
        $qty = $promoDataItem->getQtyToProcess();
        if (isset($params[self::KEY_QTY_ITEM_PREFIX . $productId])
            && $params[self::KEY_QTY_ITEM_PREFIX . $productId] <= $qty
        ) {
            $qty = $params[self::KEY_QTY_ITEM_PREFIX . $productId];
        }

        return (float)$qty;
    }
}
