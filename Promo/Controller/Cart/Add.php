<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Controller\Cart;

use Amasty\Promo\Helper\Cart;
use Amasty\Promo\Helper\Messages;
use Amasty\Promo\Model\PromoItemRepository;
use Amasty\Promo\Model\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;

/**
 * Add Promo Items action
 */
class Add extends \Magento\Framework\App\Action\Action
{
    public const MESSAGE_GROUP = 'ammessenger';
    public const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';

    /**
     * @var Registry
     */
    protected $promoRegistry;

    /**
     * @var Cart
     */
    protected $promoCartHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

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
        Registry $promoRegistry,
        Cart $promoCartHelper,
        ProductRepositoryInterface $productRepository,
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        Messages $promoMessagesHelper,
        PromoItemRepository $promoItemRepository
    ) {
        parent::__construct($context);
        $this->promoRegistry = $promoRegistry;
        $this->promoCartHelper = $promoCartHelper;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getItemsRequest();

        $updateTotalQty = false;

        $quote = $this->checkoutSession->getQuote();

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        try {
            $itemsForAdd = [];
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
                    $updateTotalQty = true;
                    $requestOptions = array_intersect_key($params, array_flip($this->requestOptions));
                    $itemsForAdd[] = $product->getSku();
                    $this->promoCartHelper->addProduct(
                        $product,
                        $qty,
                        $promoDataItem,
                        $requestOptions,
                        $quote
                    );
                }
            }

            if ($updateTotalQty) {
                $this->quoteRepository->save($quote);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage(), self::MESSAGE_GROUP);
        }

        if (!$this->isPromoItemsAddedInQuote($quote->getAllItems(), $itemsForAdd)) {
            $this->messageManager->addErrorMessage(
                __(
                    'Free gift couldn\'t be added to the cart.' .
                    'Please try again or contact the administrator for more information.'
                )->render(),
                self::MESSAGE_GROUP
            );
        }

        return $resultRedirect;
    }

    /**
     * @param array $items
     * @param array $itemsForAdd
     * @return bool
     */
    private function isPromoItemsAddedInQuote(array $items, array $itemsForAdd): bool
    {
        $addedItems = 0;
        foreach ($items as $item) {
            if (in_array($item->getProduct()->getData('sku'), $itemsForAdd, true)) {
                $addedItems++;
            }
        }

        return (bool)$addedItems;
    }

    /**
     * @return array
     */
    protected function getItemsRequest()
    {
        return $this->getRequest()->getParam('data') ?? [$this->getRequest()->getParams()];
    }

    /**
     * @param string $sku
     * @param array $params
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemData|null
     * @since 2.5.0 promo item data is filtering by rule_id and sku instead only by sku
     * @since 2.14.0 promo item data can be obtained by quoteId
     */
    protected function getPromoDataItem($sku, $params, int $quoteId)
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
    protected function getQtyToAdd($promoDataItem, $params, $productId)
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
