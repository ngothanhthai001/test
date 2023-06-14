<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Setup\Patch\Data;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ClearActionsForWholeCart implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }
    
    /**
     * @return void
     */
    public function apply(): void
    {
        $emptyActions = '{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Product\\\\Combine"'
            . ',"attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}';

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('salesrule'),
            [
                'actions_serialized' => $emptyActions
            ],
            'simple_action = ' . $this->moduleDataSetup->getConnection()->quote(GiftRuleInterface::WHOLE_CART)
        );
    }
    
    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
    
    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
