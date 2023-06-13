<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Multiple Coupons for Magento 2
 */

namespace Amasty\Coupons\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface DiscountBreakdownLineInterface extends ExtensibleDataInterface
{
    /**
     * Constants used as key into $_data
     */
    public const RULE_ID = 'rule_id';
    public const RULE_NAME = 'rule_name';
    public const RULE_AMOUNT = 'rule_amount';
    public const RULE_LABEL = 'rule_label';

    /**
     * @return string|null
     */
    public function getRuleName(): ?string;

    /**
     * @param string $ruleName
     * @return void
     */
    public function setRuleName(string $ruleName): void;

    /**
     * @return string
     */
    public function getRuleAmount(): string;

    /**
     * @param string $ruleAmount
     * @return void
     */
    public function setRuleAmount(string $ruleAmount): void;
}
