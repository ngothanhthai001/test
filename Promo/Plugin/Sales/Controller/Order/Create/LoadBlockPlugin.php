<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Plugin\Sales\Controller\Order\Create;

use Magento\Sales\Controller\Adminhtml\Order\Create\LoadBlock;
use Magento\Backend\Model\Session\Quote;

class LoadBlockPlugin
{
    /**
     * @var Quote
     */
    private $backendQuote;

    public function __construct(Quote $backendQuote)
    {
        $this->backendQuote = $backendQuote;
    }

    public function beforeExecute(LoadBlock $subject)
    {
        $params = $subject->getRequest()->getPostValue();

        if ($this->backendQuote->getPromoUpdate()) {
            foreach ($this->backendQuote->getPromoUpdate() as $itemId => $qty) {
                if (!empty($params['item'][$itemId])) {
                    $params['item'][$itemId]['qty'] += $qty;
                }
            }
            $this->backendQuote->setPromoUpdate(null);
            $subject->getRequest()->setPostValue($params);
        }
    }
}
