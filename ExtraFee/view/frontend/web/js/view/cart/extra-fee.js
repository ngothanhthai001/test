/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'ko',
    'underscore',
    'Mageplaza_ExtraFee/js/view/abstract-extra-fee',
    'Mageplaza_ExtraFee/js/action/update-extra-fee-rule',
    'Mageplaza_ExtraFee/js/model/extra-fee',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate'
], function ($, ko, _, Component, updateRule, extraFee, quote, additionalValidators, $t) {
    'use strict';


    return Component.extend({
        defaults: {
            template: 'Mageplaza_ExtraFee/cart/extra-fee'
        },
        ruleConfig: extraFee.ruleConfig,
        area: '3',
        isCheckoutCart: $('body').hasClass('checkout-cart-index'),
        errorValidationMessage: ko.observable(false),

        initialize: function () {
            this._super();
            additionalValidators.registerValidator(this);
            if (this.isCheckoutCart) {
                updateRule(this.area);
                extraFee.isDuplicate = true;
            }
        },
        /**
         * Init observer event
         * @return {exports}
         */
        initObservable: function () {
            this._super();
            var self = this;
            if (!this.isCheckoutCart) {
                return this;
            }
            quote.totals.subscribe(function () {
                if (extraFee.isDuplicate !== true) {
                    updateRule(self.area);
                }
                extraFee.isDuplicate = false;
            });

            return this;
        },
        validate: function () {
            var isValid = true;
            $('#mp-extra-fee .mp-extra-fee-required').each(function () {
                if (!$(this).find('input:checked').length) {
                    isValid = false;
                }
            });
            if (!isValid) {
                this.errorValidationMessage($t('Please choose at least one option for each require extra fee'));
            }
            return isValid;
        }
    });
});