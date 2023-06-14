/**
 * Gifts grid select column
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/multiselect',
    'Amasty_Promo/js/action/quantity',
    'Amasty_Promo/js/model/selected-items',
    'Amasty_Promo/js/utils'
], function ($, _, registry, Select, quantityInputActions, selectedItemsModel, utils) {
    'use strict';

    return Select.extend({
        defaults: {
            rowSelectorPattern: 'tr[data-repeat-index=\'{rowIndex}\']',
            checkboxSelector: '.ampromo-gift-cell [data-action="select-row"]',
            headerTmpl: 'Amasty_Promo/modal/grid/column/select-header',
            modules: {
                mainModal: 'index = amasty_promo_gift_selector_modal',
                configureModal: 'amasty_promo_items.amasty_promo_items.amasty_promo_gift_configure_product_modal',
                // eslint-disable-next-line max-len
                productsListing: 'amasty_promo_items.amasty_promo_items.amasty_promo_gift_selector_modal.assign_promo_listing'
            },
            listens: {
                '${$.provider}:resetGrid': 'clearSelectedItems'
            }
        },

        /**
         * Open configuration modal if product is not simple
         * @param {Object} record
         * @returns {void}
         */
        openConfigModalOnSelect: function (record) {
            if (utils.isConfigurable(record)) {
                this.configureModal().loadConfigurationAndOpenModal(record['entity_id']);
            }
        },

        /**
         * Save product qty to listing
         * @param {Number} productId
         * @param {Number} qty
         * @returns {void}
         */
        saveQtyToListing: function (productId, qty) {
            if (!qty) {
                return;
            }

            this.productsListing().saveQty(productId, qty);
        },

        /** @inheritdoc */
        onSelectedChange: function (selected) {
            var checkedItemId,
                uncheckedItemId,
                record,
                self = this;

            if (window.saveInProgress === true) {
                return this;
            }

            if (!this.prevSelectedArray) {
                this.prevSelectedArray = [];
            }

            this._super();

            checkedItemId = _.difference(selected, this.prevSelectedArray)[0];
            uncheckedItemId = _.difference(this.prevSelectedArray, selected)[0];

            if (checkedItemId) {
                record = this.getRecordByItemId(+checkedItemId);

                this.toggleItemHandler(record, true);
            }

            if (uncheckedItemId) {
                record = this.getRecordByItemId(+uncheckedItemId);

                this.toggleItemHandler(record, false);
            }

            this.prevSelectedArray = _.clone(selected);

            if (this.mainModal().availablePromoCount() <= 0) {
                _.each(this.rows(), function (row, index) {
                    if (!self.isSelected(index, row)) {
                        $(self.rowSelectorPattern.replace('{rowIndex}', index)
                            + ' '
                            +  self.checkboxSelector).attr('disabled', true);
                    }
                })
                // _.each($(this.checkboxSelector), function (checkbox) {
                //     if (!checkbox.checked) {
                //         checkbox.disabled = true;
                //     }
                // });
            } else {
                _.each($(this.checkboxSelector), function (checkbox) {
                    checkbox.disabled = false;
                });
            }

            return this;
        },

        /**
         * @param {Number} itemId
         * @returns {*}
         */
        getRecordByItemId: function (itemId) {
            return this.rows().filter(function (row) {
                return +row['entity_id'] === +itemId;
            })[0];
        },

        /**
         * @param {Object} record
         * @param {Boolean} checkedState
         * @returns {void}
         */
        toggleItemHandler: function (record, checkedState) {
            var productId = +record['entity_id'],
                productQty = +$('[data-ampromo-product="' + productId + '"]').val();

            if (checkedState) {
                this.openConfigModalOnSelect(record);
                this.saveQtyToListing(productId, productQty);
                selectedItemsModel.addSelectedItem(productId, productQty);
            } else {
                selectedItemsModel.removeSelectedItem(productId);
            }

            quantityInputActions.toggleQtyInput(productId, checkedState);
            quantityInputActions.setQtyValue(productId, checkedState ? 1 : null);
        },

        clearSelectedItems: function () {
            selectedItemsModel.clearSelectedItems();
            this.deselectAll();
        }
    });
});
