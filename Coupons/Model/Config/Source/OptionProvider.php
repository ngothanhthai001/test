<?php
/**

 */
namespace Amasty\Coupons\Model\Config\Source;

class OptionProvider
{
    /**
     * @var \Amasty\CommonRules\Model\OptionProvider\Pool
     */
    protected $poolOptionProvider;

    /**
     * OrderLimitations constructor.
     * @param \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider
     */
    public function __construct(
        \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider
    )
    {
        $this->poolOptionProvider = $poolOptionProvider;
    }
    
    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        return $this->poolOptionProvider->getOptionsByProviderCode('sales_rules');
    }
}
