/**
 * Gifts grid column
 */

define([
    'jquery',
    'Magento_Ui/js/grid/columns/column'
], function ($, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            modules: {
                selectColumn: '${$.parentName}.select',
                mainModal: 'index = amasty_promo_gift_selector_modal',
            }
        },

        /**
         * @param {Object} record
         * @returns {Function}
         */
        getFieldHandler: function (record) {
            return this.customColumnHandler.bind(this, record);
        },

        /**
         * Custom handler for grid cells
         * @param {Object} record
         * @returns {void}
         */
        customColumnHandler: function (record) {
            var selectColumn = this.selectColumn(),
                recordId = selectColumn.getId(record._rowIndex, true);

            if (this.mainModal().availablePromoCount() <= 0
                && !selectColumn.isSelected(recordId, false)
            ) {
                return;
            }

            this.defaultColumnHandler(record);

            if ($(event.target).attr('data-ampromo-js') === 'configure') {
                if (!selectColumn.isSelected(recordId, false)) {
                    selectColumn.select(recordId, false);
                }

                return;
            }

            selectColumn.toggleSelect(recordId, false);
        },

        /**
         * @param {Object} record
         * @returns {Null|Function}
         */
        defaultColumnHandler: function (record) {
            if (this.hasFieldAction()) {
                return this.applyFieldAction.bind(this, record._rowIndex);
            }

            return null;
        }
    });
});
