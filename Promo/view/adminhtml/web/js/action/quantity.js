/**
 * Quantity input actions
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * @param {Number} productId
         * @param {Boolean} state
         * @returns {void}
         */
        toggleQtyInput: function (productId, state) {
            $('[data-ampromo-product="' + productId + '"]').prop('disabled', !state);
        },

        /**
         * @param {Number} productId
         * @param {String} value
         * @returns {void}
         */
        setQtyValue: function (productId, value) {
            $('[data-ampromo-product="' + productId + '"]').val(value).trigger('change');
        }
    }
    ;
});
