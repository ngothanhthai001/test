<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Quote\Model\Quote;

use Amasty\Promo\Model\Backend\AllowedQtyChangeFlag;
use Magento\Quote\Model\Quote;

/**
 * Used for create in admin.
 * When allowed_qty for promo item changes.
 */
class TriggerRecollectTotals
{
    /**
     * @var AllowedQtyChangeFlag
     */
    private $allowedQtyChangeFlag;

    public function __construct(AllowedQtyChangeFlag $allowedQtyChangeFlag)
    {
        $this->allowedQtyChangeFlag = $allowedQtyChangeFlag;
    }

    public function beforeCollectTotals(Quote $quote): void
    {
        if ($this->allowedQtyChangeFlag->get()) {
            foreach ($quote->getAllAddresses() as $address) {
                $address->setData(TotalsCollectorPlugin::KEY_IS_ADDRESS_PROCESSED, false);
            }
            $quote->setTotalsCollectedFlag(false);
            $this->allowedQtyChangeFlag->set(false);
        }
    }
}
