<?php

namespace Amasty\Payrestriction\Plugin\Setup;

class UpgradeData
{
    /**
     * @var \Amasty\Base\Setup\SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    public function __construct(\Amasty\Base\Setup\SerializedFieldDataConverter $fieldDataConverter)
    {
        $this->fieldDataConverter = $fieldDataConverter;
    }

    /**
     * @param \Magento\SalesRule\Setup\UpgradeData $subject
     * @param $result
     * @return mixed
     */
    public function afterConvertSerializedDataToJson(\Magento\SalesRule\Setup\UpgradeData $subject, $result)
    {
        $this->fieldDataConverter->convertSerializedDataToJson(
            'am_payrestriction_rule',
            'rule_id',
            ['conditions_serialized']
        );

        return $result;
    }
}
