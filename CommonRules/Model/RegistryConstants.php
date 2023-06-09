<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Common Rules for Magento 2 (System)
 */

namespace Amasty\CommonRules\Model;

/**
 * Declarations of core registry keys used by the CommonRules module
 *
 */
class RegistryConstants
{
    /**
     * Rule table names for modules
     */
    public const SHIPPING_RULES_RULE_TABLE_NAME = 'amasty_shiprules_rule';
    public const SHIPPING_RESTRICTIONS_RULE_TABLE_NAME = 'amasty_shiprestriction_rule';
    public const PAYMENT_RESTRICTIONS_RULE_TABLE_NAME = 'am_payrestriction_rule';

    public const AMASTY_SPECIAL_PROMOTIONS_PRO_MODULE_NAME = 'Amasty_RulesPro';
}
