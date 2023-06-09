<?php

namespace Amasty\Payrestriction\Plugin\Payment\Model;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Payment method is available validation
 */
class MethodInterfacePlugin
{
    /**
     * @var \Amasty\Payrestriction\Model\Restrict
     */
    private $restrict;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|null
     */
    private $quote;

    public function __construct(\Amasty\Payrestriction\Model\Restrict $restrict)
    {
        $this->restrict = $restrict;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Payment\Model\MethodInterface $subject
     * @param bool $result
     * @param CartInterface|null $quote
     *
     * @return bool
     */
    public function afterIsAvailable(\Magento\Payment\Model\MethodInterface $subject, $result, $quote = null)
    {
        if ($quote === null) {
            $quote = $this->quote;
        }
        if ($result === false || $quote === null) {
            return $result;
        }

        $allowedMethods = $this->restrict->restrictMethods([$subject->getCode() => $subject], $quote);

        return isset($allowedMethods[$subject->getCode()]);
    }

    /**
     * Compatibility with Magneto 2.1 TODO remove compatibility
     *
     * @param \Magento\Payment\Model\MethodInterface $subject
     * @param null $quote
     */
    public function beforeIsAvailable(\Magento\Payment\Model\MethodInterface $subject, $quote = null)
    {
        $this->quote = $quote;
    }
}
