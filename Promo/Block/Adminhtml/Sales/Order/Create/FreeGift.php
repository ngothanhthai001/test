<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Block\Adminhtml\Sales\Order\Create;

use Amasty\Promo\Api\Data\CounterInterface;
use Amasty\Promo\Model\Quote\PromoItemCounter;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class FreeGift extends Template
{
    private const BUTTON_ACTIVE_CLASSES = 'action-default scalable action-secondary';
    private const BUTTON_INACTIVE_CLASSES = 'action-default scalable';

    /**
     * @var Quote
     */
    private $backendQuoteSession;

    /**
     * @var PromoItemCounter
     */
    private $counter;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CounterInterface
     */
    private $countData;

    public function __construct(
        Template\Context $context,
        Quote $backendQuoteSession,
        PromoItemCounter $counter,
        Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backendQuoteSession = $backendQuoteSession;
        $this->counter = $counter;
        $this->jsonSerializer = $jsonSerializer;
        $this->countData = $this->getPromoCountData();
    }

    /**
     * @return string
     */
    public function getComponentConfig(): string
    {
        $name = 'promoChooser';
        $config = [
            'Amasty_Promo/js/form/element/modal/button' => [
                'name' => $name,
                'visible' => true,
                'gifts' => [
                    'available' => $this->countData ? $this->countData->getAvailableCount() : 0,
                    'selected' => $this->countData ? $this->countData->getSelectedCount() : 0,
                ],
                'promoPrefixText' => __('Free Items'),
                'promoSuffixText' => __('Left'),
                'bodyTmpl' => 'Amasty_Promo/promo_button',
                'actions' => [
                    0 => [
                        'targetName' => 'index = amasty_promo_gift_selector_modal',
                        'actionName' => 'toggleModal'
                    ],
                    1 => [
                        'targetName' => '${ $.name }',
                        'actionName' => 'onModalOpen'
                    ]
                ]
            ]
        ];

        if (!$this->countData->getAvailableCount()) {
            unset($config['Amasty_Promo/js/form/element/modal/button']['actions']);
        }

        return $this->jsonSerializer->serialize($config);
    }

    /**
     * @return string
     */
    public function getButtonClasses(): string
    {
        $classes = self::BUTTON_ACTIVE_CLASSES;
        if (!$this->countData->getAvailableCount()) {
            $classes = self::BUTTON_INACTIVE_CLASSES;
        }

        return $classes;
    }

    /**
     * @return string
     */
    public function getButtonLabel(): string
    {
        $countOfPromo = $this->getPromoCountData();

        $label = (!$countOfPromo->getAvailableCount())
            ? __('No free gift(s) are available')
            : __(
                'Free gift(s) are available. Added %1 out of %2 items',
                $countOfPromo->getSelectedCount(),
                $countOfPromo->getSelectedCount() + $countOfPromo->getAvailableCount()
            );

        return $label->render();
    }

    /**
     * @return CounterInterface
     */
    private function getPromoCountData(): ?CounterInterface
    {
        $quote = $this->backendQuoteSession->getQuote();

        return $this->counter->getPromoCounts($quote);
    }
}
