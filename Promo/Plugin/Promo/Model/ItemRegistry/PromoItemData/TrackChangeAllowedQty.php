<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Promo\Model\ItemRegistry\PromoItemData;

use Amasty\Promo\Model\Backend\AllowedQtyChangeFlag;
use Amasty\Promo\Model\ItemRegistry\PromoItemData;

class TrackChangeAllowedQty
{
    /**
     * @var AllowedQtyChangeFlag
     */
    private $allowedQtyChangeFlag;

    public function __construct(AllowedQtyChangeFlag $allowedQtyChangeFlag)
    {
        $this->allowedQtyChangeFlag = $allowedQtyChangeFlag;
    }

    /**
     * @param PromoItemData $promoItemData
     * @param int $allowedQty
     */
    public function beforeSetAllowedQty(PromoItemData $promoItemData, $allowedQty): void
    {
        if ($promoItemData->getAllowedQty()
            && $allowedQty
            && $promoItemData->getAllowedQty() < $allowedQty
        ) {
            $this->allowedQtyChangeFlag->set(true);
        }
    }
}
