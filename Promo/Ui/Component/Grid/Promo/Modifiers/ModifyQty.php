<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Ui\Component\Grid\Promo\Modifiers;

use Amasty\Promo\Helper\Data;
use Amasty\Promo\Model\Product;
use Magento\Backend\Model\Session\Quote;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ModifyQty implements ModifierInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Quote
     */
    private $backendQuote;

    /**
     * @var Product
     */
    private $productStockProvider;

    public function __construct(
        Data $data,
        Quote $backendQuote,
        Product $productStockProvider
    ) {
        $this->data = $data;
        $this->backendQuote = $backendQuote;
        $this->productStockProvider = $productStockProvider;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $promoItemsData = $this->data->getPromoItemsDataArray($this->backendQuote->getQuote());
        foreach ($data['items'] as &$item) {
            $item['max_available_qty'] = $this->productStockProvider->checkAvailableQty(
                $item['sku'],
                $promoItemsData['promo_sku'][$item['sku']]['qty'],
                $this->backendQuote->getQuote()
            );
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
