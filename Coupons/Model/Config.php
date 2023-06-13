<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Multiple Coupons for Magento 2
 */

namespace Amasty\Coupons\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

/**
 * Module Config Provider
 */
class Config extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amcoupons/';

    public const UNIQUE_COUPONS = 'general/unique_codes';
    public const ALLOW_SAME_RULE = 'general/allow_same_rule';
    public const ALLOW_ORDER_LIMIT = 'order_limit/enabled';
    /**
     * @return string
     */
    public function getUniqueCoupons(): string
    {
        return (string)$this->getValue(self::UNIQUE_COUPONS);
    }

    /**
     * @return bool
     */
    public function isAllowCouponsSameRule(): bool
    {
        return $this->isSetFlag(self::ALLOW_SAME_RULE);
    }

    /**
     * @return bool
     */
    public function isAllowOrderLimit(): bool
    {
        return $this->isSetFlag(self::ALLOW_ORDER_LIMIT);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float|int|mixed|null
     */
    public function getLimitDiscount(\Magento\Quote\Model\Quote $quote)
    {
        $maxDiscount = null;
        if($this->isAllowOrderLimit()) {
            $subtotal = $quote->getSubtotal();
            $limitDiscount = $this->getValue('order_limit/order_limitations_value');
            if (is_numeric($limitDiscount) > 0) {
                $limitType = $this->getValue('order_limit/order_limitations_type');
                switch ($limitType) {
                    case '0':
                        $maxDiscount = $limitDiscount;
                        break;
                    case '1':
                        if($limitDiscount <= 100 ) {
                            $maxDiscount = $subtotal * $limitDiscount / 100;
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $maxDiscount;
    }

    /**
     * @return string
     */
    public function getCouponDisable()
    {
        return (string)$this->getValue('not_apply_restriction/coupon_disable');
    }

    /**
     * @return mixed
     */
    public function getCouponDisableId()
    {
        return (string)$this->getValue('not_apply_restriction/discount_id_disable');
    }


}
