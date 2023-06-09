<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\Modifiers;

/**
 * Subtotal Modifier
 */
class Subtotal implements ModifierInterface
{
    /**
     * @var string
     */
    protected $sectionConfig = '';

    /**
     * @var \Amasty\CommonRules\Model\Config
     */
    private $config;

    /**
     * Subtotal constructor.
     * @param \Amasty\CommonRules\Model\Config $config
     */
    public function __construct(\Amasty\CommonRules\Model\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $object
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function modify($object)
    {
        /** @var \Magento\Quote\Model\Quote\Address $tempObject */
        $tempObject = clone $object;

        $subtotal = $tempObject->getSubtotal();
        $baseSubtotal = $tempObject->getBaseSubtotal();
        $includeTax = $this->config->getTaxIncludeConfig($this->getSectionConfig());
        $includeDiscount = $this->config->getUseSubtotalConfig($this->getSectionConfig());

        if ($includeTax) {
            $subtotal += $tempObject->getTaxAmount();
            $baseSubtotal += $tempObject->getBaseTaxAmount();
        }

        if ($includeDiscount) {
            $subtotal += $tempObject->getDiscountAmount();
            $baseSubtotal += $tempObject->getBaseDiscountAmount();
        }

        if ($includeTax && $includeDiscount) {
            $subtotal += $tempObject->getDiscountTaxCompensationAmount();
            $baseSubtotal += $tempObject->getBaseDiscountTaxCompensationAmount();
        }

        $tempObject->setSubtotal($subtotal);
        $tempObject->setBaseSubtotal($baseSubtotal);
        $tempObject->setPackageValueWithDiscount($baseSubtotal);
        if (!$tempObject->getTotalQty()) {
            $tempObject->setTotalQty($tempObject->getQuote()->getItemsQty());
        }

        return $tempObject;
    }

    /**
     * @param $sectionConfig
     * @return $this
     */
    public function setSectionConfig($sectionConfig)
    {
        $this->sectionConfig = $sectionConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectionConfig()
    {
        return $this->sectionConfig;
    }
}
