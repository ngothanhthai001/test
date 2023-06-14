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
    'Mageplaza_ExtraFee/js/view/abstract-extra-fee',
    'Mageplaza_ExtraFee/js/action/update-extra-fee-rule',
    'Mageplaza_ExtraFee/js/model/extra-fee',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/step-navigator'
], function ($, ko, Component, updateRule, extraFee, quote, $t, additionalValidators, stepNav) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_ExtraFee/checkout/extra-fee-billing'
        },
        billingRuleConfig: extraFee.billingRuleConfig,
        errorValidationMessage: ko.observable(false),
        area: '1',

        initialize: function () {
            this._super();
            additionalValidators.registerValidator(this);
            if (window.checkoutConfig.oscConfig) {
                updateRule('1,2,3');
            } else {
                if (this.isPayment()) {
                    updateRule('2,3');
                } else {
                    updateRule('1,2,3');
                }
            }
        },
        isPayment: function () {
            var steps = stepNav.steps(),
                paymentStep = _.where(steps, {'code': 'payment'});

            return paymentStep.length && paymentStep[0].isVisible();
        },
        updateRule: function () {
            if (extraFee.isDuplicate !== true) {
                if (stepNav.getActiveItemIndex() === 1 || window.checkoutConfig.oscConfig) {
                    updateRule('1,2,3');
                } else {
                    updateRule('2,3');
                }
            }
            extraFee.isDuplicate = false;
        },
        /**
         * Init observer event
         * @return {exports}
         */
        initObservable: function () {
            this._super();
            var self = this;
            quote.totals.subscribe(function () {
                self.updateRule();
            });
            quote.shippingAddress.subscribe(function () {
                self.updateRule();
            });
            quote.paymentMethod.subscribe(function () {
                self.updateRule();
            });
            return this;
        },
        validate: function () {
            var isValid = true;
            $('#mp-extra-fee-billing .mp-extra-fee-required').each(function () {
                if (!$(this).find('input:checked').length) {
                    isValid = false;
                }
            });
            if (!isValid) {
                this.errorValidationMessage($t('Please choose at least one option for each require extra fee'));
            }
            return isValid;
        },
        afterRenderOptions: function (option, optionVal) {
            if (!extraFee.billingSelectedOptions()['rule']) {
                return;
            }
            var data = extraFee.billingSelectedOptions()['rule'];
            var type = optionVal.display_type || optionVal.type;
            if (type === '3') {
                if (data[optionVal.rule_id] == optionVal.value) {
                    $(option).attr('selected', true);
                }
            } else {
                var input = $(option).find('input');
                input.each(function () {
                    if (data[$(this).attr('rule_id')] !== undefined) {
                        if (data[$(this).attr('rule_id')] === $(this).val()) {
                            $(this).attr('checked', true);
                        }
                        if (data[$(this).attr('rule_id')][$(this).val()] !== undefined) {
                            $(this).attr('checked', data[$(this).attr('rule_id')][$(this).val()]);
                        }
                    }
                });
            }
        }
    });
});