/**
 * Gifts open modal button
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function ($, Element, registry) {
    'use strict';

    return Element.extend({
        defaults: {
            gifts: {
                available: 0,
                selected: 0
            },
            promoPrefixText: '',
            promoSuffixText: '',
            modules: {
                listingSource: 'amasty_promo_listing.amasty_promo_listing_data_source',
                mainModal: 'index = amasty_promo_gift_selector_modal',
                // eslint-disable-next-line max-len
                productsListing: 'amasty_promo_items.amasty_promo_items.amasty_promo_gift_selector_modal.assign_promo_listing'
            }
        },

        /** @inheritdoc */
        initialize: function (config, element) {
            this._super();

            registry.set(this.name, this);
            this.chooser = $(element);
            this.chooser.on('click', this.action.bind(this));

            if (this.productsListing()) {
                this.productsListing().setExternalValue({});
            }

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([ 'gifts' ]);

            return this;
        },

        /**
         * Handler to change grid rows according to changes in order creation page
         * @returns {void}
         */
        onModalOpen: function () {
            var modal = this.mainModal();

            modal.options.promoPrefixText = this.promoPrefixText;
            modal.options.promoSuffixText = this.promoSuffixText;
            this.listingSource(function (listingDataProvider) {
                if (!listingDataProvider.firstLoad) {
                    listingDataProvider.params.filters_modifier = {};
                    listingDataProvider.reload({ 'refresh': true });
                }

                listingDataProvider.trigger('resetGrid');
            });
            modal.initializedAvailablePromoCount(this.gifts().available);
        }
    });
});
