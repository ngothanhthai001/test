/**
 * Selected free gifts model
 */

define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    return {
        selectedItems: ko.observableArray([]),

        /**
         * @param {Number} productId
         * @param {Number} qty
         * @returns {void}
         */
        addSelectedItem: function (productId, qty) {
            var bufferArray = this.selectedItems();

            bufferArray.push({ productId: productId, qty: qty });

            this.selectedItems(bufferArray);
        },

        /**
         * @param {Number} productId
         * @param {Number} qty
         * @returns {void}
         */
        updateSelectedItem: function (productId, qty) {
            var bufferArray = this.selectedItems(),
                alreadySelectedIndex;

            _.each(bufferArray, function (item) {
                if (+item.productId === +productId) {
                    alreadySelectedIndex = bufferArray.indexOf(item);
                }
            });

            if (!_.isUndefined(alreadySelectedIndex) && alreadySelectedIndex !== -1) {
                bufferArray[alreadySelectedIndex].qty = qty;
            }

            this.selectedItems(bufferArray);
        },

        /**
         * @param {Number} productId
         * @returns {void}
         */
        removeSelectedItem: function (productId) {
            var bufferArray = this.selectedItems();

            bufferArray = bufferArray.filter(function (item) {
                return +item.productId !== +productId;
            });

            this.selectedItems(bufferArray);
        },

        clearSelectedItems: function () {
            this.selectedItems([]);
        },

        /**
         * @param {Number} productId
         * @returns {Boolean}
         */
        isItemSelected: function (productId) {
            var selectedItems = this.selectedItems();

            return _.some(selectedItems, function (item) {
                return +item.productId === +productId;
            });
        }
    };
});
