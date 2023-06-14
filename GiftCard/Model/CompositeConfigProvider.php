<?php

namespace Mageplaza\GiftCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function getConfig()
    {
        $config = [
            'mpgiftcard_code' => $this->getCardCode(),
        ];
        return $config;
    }

    public function getCardCode()
    {
        // $addressId = null;
        // $quote = $this->checkoutSession->getQuote();
        // $isQuotation = $quote->getWikiQuotationQuote();
        // if ($isQuotation) {
        //     $addressId = $quote->getShippingAddress()->getCustomerAddressId();
        // }
        // return $addressId ?? null;

        $code = $this->checkoutSession->getRemoveGiftCardData();
        return $code ?? null;
    }
}
