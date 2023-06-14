<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Sales\Block\Adminhtml\Order\Create\Items;

use Amasty\Promo\Helper\Item as PromoItemHelper;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid;

class DisableConfigureButton
{
    /**
     * @var PromoItemHelper
     */
    private $itemHelper;

    public function __construct(PromoItemHelper $itemHelper)
    {
        $this->itemHelper = $itemHelper;
    }

    /**
     * @see \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::getConfigureButtonHtml()
     *
     * @param Grid $subject
     * @param string $result
     * @param Item $item
     * @return string
     */
    public function afterGetConfigureButtonHtml(Grid $subject, $result, $item): string
    {
        return !$this->itemHelper->isPromoItem($item) ? $result : '';
    }
}
