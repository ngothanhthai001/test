/**
 * Save data component
 */

define([
    'underscore',
    'Magento_Ui/js/form/components/insert-listing',
    'Amasty_Promo/js/action/add-products'
], function (_, InsertListing, addProducts) {
    'use strict';

    return InsertListing.extend({
        defaults: {
            configuredItemIds: [],
            configuredProductsStorage: [],
            addUrl: '',
            optionNameRegex: '^[a-z_A-Z]*',
            optionIdRegex: '\\d+'
        },

        /** @inheritdoc */
        save: function () {
            var payload = {},
                productId,
                itemData,
                optionName,
                optionId;

            window.saveInProgress = true;

            this._super();

            if (_.isEmpty(this.externalValue())) {
                window.saveInProgress = false;

                return;
            }

            _.each(this.externalValue(), function (item) {
                productId = parseInt(item.entity_id);
                itemData = { product_id: productId };

                if (!_.isEmpty(this.configuredProductsStorage[productId])) {
                    itemData['bundle_option'] = {};
                    itemData['bundle_option_qty'] = {};

                    _.each(this.configuredProductsStorage[productId], function (configuration) {
                        optionName = configuration.name.match(this.optionNameRegex)
                            ? configuration.name.match(this.optionNameRegex)[0]
                            : null;
                        optionId = configuration.name.match(this.optionIdRegex)
                            ? configuration.name.match(this.optionIdRegex)[0]
                            : null;

                        if (_.isUndefined(itemData[optionName])) {
                            itemData[optionName] = {};
                        }

                        if (optionName !== null && optionId !== null) {
                            itemData[optionName][optionId] = configuration.value;
                        } else {
                            itemData[configuration.name] = configuration.value;
                        }
                    }.bind(this));
                }

                payload[item.sku] = itemData;
            }.bind(this));

            addProducts(this.addUrl, payload);
        },

        /**
         * Save product configuration for further adding
         * @param {Number} productId
         * @param {Object} formData
         * @returns {void}
         */
        saveConfiguration: function (productId, formData) {
            this.configuredProductsStorage[productId] = formData;
            this.configuredItemIds.push(productId);
            this.configuredItemIds = _.uniq(this.configuredItemIds);
        },

        /**
         * Save qty from grid input to payload
         * @param {Number} productId
         * @param {Number} qty
         * @returns {void}
         */
        saveQty: function (productId, qty) {
            var savedProductConfig = this.configuredProductsStorage[productId],
                qtyParamIndex;

            if (_.isUndefined(savedProductConfig)) {
                savedProductConfig = [ { name: 'qty', value: qty } ];
                this.saveConfiguration(productId, savedProductConfig);

                return;
            }

            qtyParamIndex = _.findIndex(savedProductConfig, function (param) {
                return param.name === 'qty';
            });

            if (qtyParamIndex !== -1) {
                savedProductConfig[qtyParamIndex].value = qty;
            } else {
                savedProductConfig[qtyParamIndex].push({ name: 'qty', value: qty });
            }

            this.saveConfiguration(productId, savedProductConfig);
        }
    });
});
