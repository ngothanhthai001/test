/**
 * Gifts grid name column
 */

define([
    'jquery',
    'uiRegistry',
    'Amasty_Promo/js/modal/grid/column',
    'Amasty_Promo/js/utils',
    'Amasty_Promo/js/model/selected-items'
], function ($, registry, Column, utils, selectedItemsModel) {
    'use strict';

    return Column.extend({
        defaults: {
            modules: {
                configureModal: 'amasty_promo_items.amasty_promo_items.amasty_promo_gift_configure_product_modal',
                mainModal: 'index = amasty_promo_gift_selector_modal',

            }
        },

        /**
         * Check configurable type
         * @param {Object} record
         * @returns {boolean}
         */
        isConfigurableType: function (record) {
            return utils.isConfigurable(record);
        },

        /**
         * @param {Object} record
         * @param {Boolean} state
         * @returns {void}
         */
        toggleQtyEnabledState: function (record, state) {
            var qtyFieldSelector = '#qty' + record.entity_id;

            if (!$(qtyFieldSelector)[0]) {
                $.async('#qty' + record['entity_id'], function (qtyField) {
                    $(qtyField).prop('disabled', !state);
                });

                return;
            }

            $(qtyFieldSelector).prop('disabled', !state);
        },

        /**
         * Open configure modal
         * @param {String} productId
         * @returns {void}
         */
        openConfigureModal: function (productId) {
            if (this.mainModal().availablePromoCount() <= 0 && !selectedItemsModel.isItemSelected(productId)) {
                return;
            }
            this.configureModal().loadConfigurationAndOpenModal(productId);
        }
    });
});
