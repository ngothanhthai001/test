<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model\Rule\Factory;

/**
 * Interface HandleFactoryInterface
 */
interface HandleFactoryInterface
{
    public const CUSTOMER_HANDLE = 'customer';
    public const ORDERS_HANDLE = 'orders';

    public const TOTAL_COMBINE_HANDLE = 'total';

    /**
     * @param string $type
     * @return array
     */
    public function create($type = self::CUSTOMER_HANDLE);
}
