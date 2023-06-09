<?php
namespace Amasty\Payrestriction\Block\Adminhtml\Rule\Edit\Tab;

use Amasty\Payrestriction\Model\RegistryConstants as PayrestrictionRegistryConstants;
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\Conditions as CommonRulesCondition;

class Conditions extends CommonRulesCondition
{
    public function _construct()
    {
        $this->setRegistryKey(PayrestrictionRegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }
}
