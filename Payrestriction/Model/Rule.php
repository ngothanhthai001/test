<?php

namespace Amasty\Payrestriction\Model;

class Rule extends \Amasty\CommonRules\Model\Rule
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Payrestriction\Model\ResourceModel\Rule::class);
        $this->subtotalModifier->setSectionConfig(\Amasty\Payrestriction\Model\RegistryConstants::SECTION_KEY);
    }

    /**
     * @param \Magento\Payment\Model\Method\AbstractMethod|\Magento\Payment\Model\MethodInterface $method
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function restrict($method)
    {
        $ruleMethods = explode(',', $this->getMethods());

        return in_array($method->getCode(), $ruleMethods);
    }
}
