<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Controller\Popup;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;

class Reload extends Action
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Layout $layout,
        \Amasty\Promo\Helper\Data $helper,
        \Amasty\Promo\Model\Config $config,
        Session $checkoutSession = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->helper = $helper;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession ?? ObjectManager::getInstance()->get(Session::class);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        $returnUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);

        if (!$returnUrl) {
            $jsonResult->setHttpResponseCode(403);
            return $jsonResult;
        }

        $quote = $this->checkoutSession->getQuote();

        $products = $this->helper->getPromoItemsDataArray($quote);
        $rawContent = '';
        if ($products['common_qty']) {
            $this->layout->getUpdate()->addHandle('amasty_promo_popup_reload');
            /** @var \Amasty\Promo\Block\Items $popupBlock */
            $popupBlock = $this->layout->getBlock('ampromo.items');
            $popupBlock->setData('current_url', $returnUrl);

            $rawContent = $popupBlock->toHtml();
        }

        $autoOpenPopup = false;
        if ((bool)$this->helper->getNewItems((int)$quote->getId()) && $this->config->isAutoOpenPopup()) {
            $autoOpenPopup = true;
        }

        $jsonResult->setData(
            ['popup' => $rawContent, 'products' => $products, 'autoOpenPopup' => $autoOpenPopup],
            true
        );

        return $jsonResult;
    }
}
