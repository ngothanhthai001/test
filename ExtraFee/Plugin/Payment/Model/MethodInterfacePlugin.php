<?php

namespace Mageplaza\ExtraFee\Plugin\Payment\Model;

use Magento\Backend\Model\Session\Quote as BackendModelSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\ExtraFee\Helper\Data;
use Mageplaza\ExtraFee\Model\RuleFactory;
use Mageplaza\ExtraFee\Model\Rule;
use Magento\Checkout\Model\Session as CheckoutSession;

class MethodInterfacePlugin
{

    /**
     * @var BackendModelSession
     */
    protected BackendModelSession $backendModelSession;

    /**
     * @var RuleFactory
     */
    protected RuleFactory $ruleFactory;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;
    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;


    public function __construct(
        BackendModelSession $backendModelSession,
        RuleFactory $ruleFactory,
        CustomerSession $customerSession,
        CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession
    )
    {
        $this->backendModelSession = $backendModelSession;
        $this->ruleFactory         = $ruleFactory;
        $this->customerSession     = $customerSession;
        $this->cartRepository        = $cartRepository;
        $this->checkoutSession        = $checkoutSession;
    }
    /**
     * @param \Magento\Payment\Model\MethodInterface $subject
     * @param $result
     * @return false|string
     */
    public function afterGetTitle(\Magento\Payment\Model\MethodInterface $subject, $result)
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if ($result === false || $quote === null) {
            return $result;
        }

        $backendModelSession = $this->backendModelSession->getQuote();
        $ruleCollection = $this->ruleFactory->create()->getCollection()
            ->setOrder('priority', 'ASC');
        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $stores = explode(',', $rule->getStoreIds());
            $customerGroups = explode(',', $rule->getCustomerGroups());
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            } elseif ($backendModelSession->getId()) {
                $customerGroupId = $backendModelSession->getCustomerGroupId();
            } else {
                $customerGroupId = 0;
            }
            $storeId = $quote->getStoreId();
            if (!$rule->getStatus()
                || !in_array($customerGroupId, $customerGroups, false)
                || !(in_array($storeId, $stores, false) || in_array('0', $stores, true))
            ) {
                continue;
            }
            foreach ($rule->getConditions()->getConditions() as $conditions) {
                if ($conditions->getAttribute() == 'payment_method' && $conditions->getValue() == $subject->getCode()) {
                    $label = isset(Data::jsonDecode($rule->getLabels())[$quote->getStoreId()])
                        ? Data::jsonDecode($rule->getLabels())[$quote->getStoreId()] : $rule->getName();
                    return $subject->getConfigData('title') . ' ('. $label .')';
                };
            }
        }
        return $result;
    }
}
