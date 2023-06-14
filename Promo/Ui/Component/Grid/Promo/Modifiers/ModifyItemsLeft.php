<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid\Promo\Modifiers;

use Amasty\Promo\Helper\Data;
use Magento\Backend\Model\Session\Quote;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ModifyItemsLeft implements ModifierInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Quote
     */
    private $backendQuoteSession;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data,
        Quote $backendQuoteSession
    ) {
        $this->data = $data;
        $this->backendQuoteSession = $backendQuoteSession;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $promoItemsData = $this->data->getPromoItemsDataArray($this->backendQuoteSession->getQuote());
        foreach ($data['items'] as &$item) {
            $item['items_left'] = $promoItemsData['promo_sku'][$item['sku']]['qty'];
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
