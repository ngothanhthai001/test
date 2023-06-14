<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Sales\Block\Adminhtml\Order\Create\Billing\Method;

use Amasty\Promo\Block\Adminhtml\Sales\Order\Create\FreeGift as FreeGiftBlock;
use Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form;

/**
 * Insert Delivery Information Block for Create Order
 */
class FormPlugin
{
    /**
     * @param Form $subject
     * @param string $html
     * @return string
     * @see Form
     *
     */
    public function afterToHtml(
        Form $subject,
        string $html
    ): string {
        $insertBlock = $subject->getLayout()->createBlock(FreeGiftBlock::class);
        $insertBlock->setTemplate('Amasty_Promo::free_gift.phtml');
        $html .= $insertBlock->toHtml();

        return $html;
    }
}
