/**
 * "Items Left" column component
 */

define([
    'jquery',
    'Amasty_Promo/js/modal/grid/column'
], function ($, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            listens: {
                '${$.parentName}.qty:qtyUpdated': 'updateItemsLeftCounter'
            }
        },

        /**
         * @param {Object} record
         * @returns {Number}
         */
        getInitialCount: function (record) {
            var qtyValue = $('#qty' + record['entity_id']).val();

            return +record['max_available_qty'] - +qtyValue >= 0
                ? record['max_available_qty'] - +qtyValue
                : 0;
        },

        /**
         * Update "Items Left" column after qty column change
         * @param {Object} data
         * @returns {void}
         */
        updateItemsLeftCounter: function (data) {
            var leftElement = $('#ampromo-left-' + data.productId);

            leftElement.html(+leftElement.attr('data-ampromo-max') - +data.count);
        }
    });
});
