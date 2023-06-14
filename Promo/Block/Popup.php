<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;

/**
 * Popup Style
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    public const POPUP_ONE_BY_ONE = 0;
    public const POPUP_MULTIPLE = 1;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $modelConfig;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var Add
     */
    private $promoAddBlock;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Template\Context $context,
        \Amasty\Promo\Model\Config $modelConfig,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Amasty\Promo\Block\Add $promoAddBlock,
        Session $checkoutSession,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->modelConfig = $modelConfig;
        $this->promoHelper = $promoHelper;
        $this->promoAddBlock = $promoAddBlock;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer ?? \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @return Add
     */
    public function getPromoAddBlock()
    {
        return $this->promoAddBlock;
    }

    /**
     * @return int|null
     */
    public function getCountersMode()
    {
        return $this->modelConfig->getScopeValue("messages/display_remaining_gifts_counter");
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getPopupName()
    {
        $popupTitle = $this->modelConfig->getPopupName();

        if (!$popupTitle) {
            $popupTitle = __('Free Items');
        }

        return $popupTitle;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $newItems = $this->promoHelper->getNewItems((int)$this->getQuote()->getId());

        return $newItems ? count($newItems) : 0;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasQuoteItems(): bool
    {
        return (bool)$this->checkoutSession->getQuote()->getAllVisibleItems();
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function jsonSerialize($data)
    {
        return $this->serializer->serialize($data);
    }

    public function getQuote(): Quote
    {
        return $this->checkoutSession->getQuote();
    }
}
