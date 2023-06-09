<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\Modifiers;

/**
 * Interface ModifierInterface
 */
interface ModifierInterface
{
    /**
     * Modify Object
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Framework\DataObject
     */
    public function modify($object);
}
