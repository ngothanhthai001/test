<?php
namespace Amasty\Payrestriction\Block\Adminhtml\Rule\Edit\Tab;

use Amasty\Payrestriction\Model\RegistryConstants;
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\Coupons as CommonRulesCoupons;

class Coupons extends CommonRulesCoupons
{
    public function _construct()
    {
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }
}