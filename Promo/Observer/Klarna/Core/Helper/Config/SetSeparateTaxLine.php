<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Observer\Klarna\Core\Helper\Config;

use Klarna\Core\Api\VersionInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Set separate tax line option for klarna totals equality
 * \Klarna\Core\Model\Checkout\Orderline\Items::153
 *
 * event name kp_load_version_details
 */
class SetSeparateTaxLine implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        /** @var VersionInterface $optionsObject */
        $optionsObject = $observer->getData('options');
        $optionsObject->setSeparateTaxLine(true);
    }
}
