<?php
/**

 */
namespace Amasty\Coupons\Model\Config\Source;

class OrderLimitations
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
            ['value' => '0', 'label' => __('Total Discount')],
            ['value' => '1', 'label' => __('Yes (percent of checkout sum)')],
        ];

        return $data;
    }
}
