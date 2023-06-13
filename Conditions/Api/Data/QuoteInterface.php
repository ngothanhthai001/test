<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Advanced Conditions for Magento 2
 */

namespace Amasty\Conditions\Api\Data;

interface QuoteInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    public const ITEM_ID = 'id';

    public const QUOTE_ID = 'quote_id';

    public const PAYMENT_CODE = 'payment_code';

    /**#@-*/

    /**
     * Get quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set quote id
     *
     * @param $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Get payment code
     *
     * @return string
     */
    public function getPaymentCode();

    /**
     * Set payment code
     *
     * @param $paymentCode
     *
     * @return $this
     */
    public function setPaymentCode($paymentCode);
}
