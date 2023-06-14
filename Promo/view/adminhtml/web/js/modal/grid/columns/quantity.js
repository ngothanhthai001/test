/**
 * Gifts grid qty column
 */

define([
    'jquery',
    'Amasty_Promo/js/modal/grid/column',
    'Amasty_Promo/js/model/selected-items'
], function ($, Column, selectedItemsModel) {
    'use strict';

    return Column.extend({
        defaults: {
            value: null,
            disabled: true,
            draggable: false,
            modules: {
                // eslint-disable-next-line max-len
                productsListing: 'amasty_promo_items.amasty_promo_items.amasty_promo_gift_selector_modal.assign_promo_listing'
            }
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('qtyUpdated');

            return this;
        },

        /**
         * @param {Event} event
         * @param {UIClass} component
         * @returns {void}
         */
        onInput: function (event, component) {
            var targetElement = event.target,
                value = targetElement.value === '' ? null : +targetElement.value,
                productId = targetElement.getAttribute('data-ampromo-product');

            if (value > targetElement.max) {
                targetElement.value = targetElement.max;
            }

            if (value <= 0 && !(value === null)) {
                targetElement.value = 1;
            }

            component.qtyUpdated({ 'productId': productId, 'count': targetElement.value });
            component.productsListing()
                .saveQty(productId, targetElement.value);

            if (selectedItemsModel.isItemSelected(+productId)) {
                selectedItemsModel.updateSelectedItem(+productId, +targetElement.value);
            }
        },

        /**
         * @param {UIClass} component
         * @param {Event} event
         * @returns {void}
         */
        onChange: function (component, event) {
            component.onInput(event, component);
        },

        onClick: function () {
            // Prevent item select by qty input click
            event.stopPropagation();
        },

        /**
         * @param {Number} productId
         * @returns {HTMLElement}
         */
        getElementByProductId: function (productId) {
            return document.getElementById(this.index + productId);
        }
    });
});
