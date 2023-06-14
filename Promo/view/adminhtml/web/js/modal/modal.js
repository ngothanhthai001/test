/**
 * Main modal component
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modal-component',
    'Amasty_Promo/js/model/selected-items',
    'mage/template',
    'text!Amasty_Promo/template/modal/counter.html'
], function ($, _, modalComponent, selectedItemsModel, template, counterTmpl) {
    'use strict';

    return modalComponent.extend({
        defaults: {
            initializedAvailablePromoCount: 0,
            availablePromoCount: 0,
            listens: {
                initializedAvailablePromoCount: 'initAvailableCounter',
                availablePromoCount: 'onChangeAvailablePromoCount'
            },
            options: {
                promoPrefixText: '',
                promoSuffixText: '',
                promoCounterWrapperClass: 'ampromo-counter-wrapper',
                promoCounterClass: 'ampromo-gifts-counter',
                displayedPromoCount: 0
            },
            css: {
                availableCounter: '-available',
                notAvailableCounter: '-not-available'
            },
            counterTmpl: counterTmpl
        },

        /** @inheritdoc */
        initModal: function () {
            this._super();

            $('.ampromo-gifts-modal .page-main-actions')
                .append('<div class="' + this.options.promoCounterWrapperClass + '"></div>');
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe(['availablePromoCount', 'initializedAvailablePromoCount']);

            selectedItemsModel.selectedItems.subscribe(this.selectedItemsObserver.bind(this));

            return this;
        },

        initAvailableCounter: function () {
            this.availablePromoCount(this.initializedAvailablePromoCount());
        },

        /**
         * Add Promo Counter Element to slide in modal window
         * @returns {void}
         */
        onChangeAvailablePromoCount: function () {
            if (this.availablePromoCount() > this.initializedAvailablePromoCount()) {
                this.availablePromoCount(this.initializedAvailablePromoCount());

                return; // prevent endless loop
            }

            if (+this.availablePromoCount() === +this.initializedAvailablePromoCount()) {
                this.toggleActionButtonState(false);
            }

            if (this.availablePromoCount() < 0) {
                this.availablePromoCount(0);

                return; // prevent endless loop
            }

            this.options.displayedPromoCount = this.availablePromoCount();
            this.updatePromoCounterLabel(_.clone(this.options));
        },

        /**
         * @param {Object} data
         * @returns {void}
         */
        updatePromoCounterLabel: function (data) {
            var mainActionBlock = $('.' + data.promoCounterWrapperClass);

            if (this.availablePromoCount() > 0) {
                data.promoCounterClass += ' ' + this.css.availableCounter;
            } else {
                data.promoCounterClass += ' ' + this.css.notAvailableCounter;
            }

            mainActionBlock.html(
                template(this.counterTmpl, { data: data })
            );
        },

        /**
         * @param {Array} selectedItems
         * @returns {void}
         */
        selectedItemsObserver: function (selectedItems) {
            this.toggleActionButtonState(!!selectedItems.length);
            this.availablePromoCount(this.initializedAvailablePromoCount() - this.getAllSelectedQty(selectedItems));
        },

        /**
         * @param {Array} selectedItems
         * @returns {Number}
         */
        getAllSelectedQty: function (selectedItems) {
            var sumQty = 0;

            _.each(selectedItems, function (item) {
                sumQty += +item.qty;
            });

            return sumQty;
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        toggleActionButtonState: function (state) {
            var addButton = $('.ampromo-gifts-modal .page-main-actions .action-primary');

            addButton.attr('disabled', !state);
        }
    });
});
