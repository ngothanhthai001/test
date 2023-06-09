<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\Modifiers;

/**
 * Address Modifier
 */
class Address implements \Amasty\CommonRules\Model\Modifiers\ModifierInterface
{
    /**
     * @param \Magento\Framework\DataObject $object
     * @param null $rateAddress
     * @return \Magento\Framework\DataObject
     */
    public function modify($object, $rateAddress = null)
    {
        if ($rateAddress) {
            $object->setData($object->getData() + $rateAddress->getData());
        }

        return $object;
    }
}
