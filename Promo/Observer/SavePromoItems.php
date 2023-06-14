<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Observer;

use Amasty\Promo\Model\PromoItemRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SavePromoItems implements ObserverInterface
{
    /**
     * @var PromoItemRepository
     */
    private $promoItemRepository;

    public function __construct(
        PromoItemRepository $promoItemRepository
    ) {
        $this->promoItemRepository = $promoItemRepository;
    }

    public function execute(Observer $observer)
    {
        $quoteId = (int)$observer->getEvent()->getQuote()->getId();
        $this->promoItemRepository->saveItems($quoteId);
    }
}
