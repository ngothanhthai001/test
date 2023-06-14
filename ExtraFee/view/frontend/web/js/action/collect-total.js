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

define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/totals',
        'Mageplaza_ExtraFee/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'Mageplaza_ExtraFee/js/model/extra-fee',
    ],
    function ($, storage, errorProcessor, totals, resourceUrlManager, quote, extraFee) {
        'use strict';

        return function (area, event) {
            var formData,
                billingEl = $('#mp-extra-fee-billing'),
                shippingEl = $('#mp-extra-fee-shipping'),
                extraFeeEl = $('#mp-extra-fee');
            if (event) {
                var checked = $(event.currentTarget),
                    isChecked = !!checked.prop('checked');
            }

            switch (area) {
                case '1':
                    formData = billingEl.serialize();
                    break;
                case '2':
                    formData = shippingEl.serialize();
                    break;
                case '3':
                    formData = extraFeeEl.serialize();
                    break;
                case '1,2,3':
                    formData = billingEl.serialize() + ',' + shippingEl.serialize() + ',' + extraFeeEl.serialize()
                    ;
                    break;
                case '2,3':
                    formData = shippingEl.serialize() + ',' + extraFeeEl.serialize()
                    ;
                    break;
            }
            totals.isLoading(true);

            var payload = {
                formData: formData,
                area: area
            };
            return storage.post(
                resourceUrlManager.getUrlForCollectTotal(quote),
                JSON.stringify(payload)
            ).done(function (totals) {
                extraFee.isDuplicate = true;
                quote.setTotals(totals);
                if (event) {
                    checked.prop('checked', isChecked);
                }
            }).fail(function (response) {
                errorProcessor.process(response);
            }).always(function () {
                totals.isLoading(false);
            });
        };
    }
);
