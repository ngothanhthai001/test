<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid;

use Amasty\Promo\Helper\Data;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing;

class PromoItemsListing extends Listing
{
    /**
     * @var Data
     */
    private $helperData;

    public function __construct(
        Data $helperData,
        ContextInterface $context,
        Quote $backendQuoteSession,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->helperData = $helperData;
        $this->backendQuoteSession = $backendQuoteSession;
    }

    public function render(): string
    {
        $promoItemsData = $this->helperData->getPromoItemsDataArray($this->backendQuoteSession->getQuote());

        if (empty($promoItemsData['promo_sku'])) {
            $returnMessage = __("Please click 'Update Items and Quantities' to refresh the cart");
            return $returnMessage->getText();
        }

        return (string)parent::render();
    }
}
