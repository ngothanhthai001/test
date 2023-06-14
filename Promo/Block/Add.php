<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */
namespace Amasty\Promo\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote;

/**
 * Popup with Promo Items initialization and link for open
 */
class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var PopupLinkEscaper
     */
    private $popupLinkEscaper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Amasty\Promo\Model\Config $config,
        array $data = [],
        PopupLinkEscaper $popupLinkEscaper = null // TODO move to not optional
    ) {
        parent::__construct($context, $data);

        $this->promoHelper = $promoHelper;
        $this->urlHelper = $urlHelper;
        $this->config = $config;
        $this->popupLinkEscaper = $popupLinkEscaper ?? ObjectManager::getInstance()->get(PopupLinkEscaper::class);
    }

    /**
     * @return bool
     */
    public function hasItems(int $quoteId)
    {
        return (bool)$this->promoHelper->getNewItems($quoteId);
    }

    /**
     * @return string
     * @deprecated since 2.5.0
     */
    public function getMessage()
    {
        return $this->getPopupLinkHtml();
    }

    /**
     * @return string
     */
    public function getPopupLinkHtml()
    {
        return $this->popupLinkEscaper->escapeHtml($this->config->getAddMessage(), ['a', 'b', 'i', 'u', 's', 'strong']);
    }

    /**
     * @return bool
     */
    public function isOpenAutomatically(int $quoteId)
    {
        return $this->config->isAutoOpenPopup() && $this->hasItems($quoteId);
    }

    /**
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return $this->urlHelper->getCurrentBase64Url();
    }

    /**
     * @return array
     */
    public function getAvailableProductQty(Quote $quote)
    {
        return $this->promoHelper->getPromoItemsDataArray($quote);
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('amasty_promo/cart/add');
    }

    /**
     * @return int|null
     */
    public function getSelectionMethod()
    {
        return $this->config->getSelectionMethod();
    }

    /**
     * Is gift counter visible
     *
     * @return int|null
     */
    public function getGiftsCounter()
    {
        return $this->config->getGiftsCounter();
    }
}
