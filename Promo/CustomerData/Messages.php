<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\CustomerData;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Messages section
 */
class Messages implements SectionSourceInterface
{
    public const VAR_ENABLED = 'messages/display_notification';

    public const VAR_TEXT = 'messages/notification_text';

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Amasty\Promo\Model\Config $config,
        \Amasty\Promo\Helper\Data $promoHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession
    ) {
        $this->config = $config;
        $this->promoHelper = $promoHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getNoticeHtml()
    {
        $placeholders = [
            '{url checkout/cart}' => $this->urlBuilder->getUrl('checkout/cart')
        ];

        $noticeHtml = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->config->getScopeValue(self::VAR_TEXT)
        );

        return $noticeHtml;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (int)$this->config->getScopeValue(self::VAR_ENABLED) === 1;
    }

    /**
     * @return int
     */
    public function getNewItemsCount()
    {
        $count = 0;
        if ($items = $this->promoHelper->getNewItems((int)$this->checkoutSession->getQuoteId())) {
            $count = count($items);
        }

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getItems() && $this->isEnabled() && $this->getNewItemsCount()) {
            return [
                'messages' => [
                    'notice' => [
                        'text' => $this->getNoticeHtml(),
                        'type' => 'notice'
                    ]
                ],
                'website_id' => $this->storeManager->getWebsite()->getId(),
                'store_id' => $this->storeManager->getStore()->getId()
            ];
        }
        return ['messages' => []];
    }
}
