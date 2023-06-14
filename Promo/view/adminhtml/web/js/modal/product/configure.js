/* global productConfigure */
/**
 * Configure modal component
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modal-component',
    'Amasty_Promo/js/action/quantity',
    'text!Amasty_Promo/template/modal/configure-form.html',
    'Magento_Catalog/catalog/product/composite/configure'
], function ($, _, modalComponent, quantityInputActions, configureFormTmpl) {
    'use strict';

    return modalComponent.extend({
        defaults: {
            currentProductId: null,
            currentProductConfig: null,
            origBlockForm: null,
            origOpenCallback: null,
            origConfirmCallback: null,
            configureFormValidState: false,
            selectors: {
                confOptionsForm: '#ampromo-configure-form',
                confOptionsFieldset: '#catalog_product_composite_configure_fields_configurable'
            },
            modules: {
                productsListing: '${ $.parentName + ".amasty_promo_gift_selector_modal.assign_promo_listing"}',
                selectColumn: 'amasty_promo_listing.amasty_promo_listing.promo_columns.select'
            }
        },

        /** @inheritdoc */
        initModal: function () {
            this._super();

            this.modal.append(configureFormTmpl);
            this.modal.find('form').mage('validation', { errorClass: 'mage-error' });
            this.addMutationHandlersForConfigurableOptions();
        },

        /**
         * When configurable option's values added -> execute observer and set saved value
         * @returns {void}
         */
        addMutationHandlersForConfigurableOptions: function () {
            var observerConfig = {
                    attributes: false,
                    childList: true,
                    subtree: false
                },
                observer = new MutationObserver(this.setSavedConfigurableOption.bind(this));

            $.async(this.selectors.confOptionsFieldset, function () {
                this.currentProductConfig = this.productsListing().configuredProductsStorage[this.currentProductId];

                _.each(this.currentProductConfig, function (configItem) {
                    var itemSelector = '[name="' + configItem.name + '"]';

                    $(itemSelector).val(configItem.value);
                    observer.observe(
                        document.querySelector(this.selectors.confOptionsForm + ' ' + itemSelector),
                        observerConfig
                    );
                }.bind(this));
            }.bind(this));
        },

        /**
         * Set configurable options from saved state
         * @param {Array} mutationsList
         * @returns {void}
         */
        setSavedConfigurableOption: function (mutationsList) {
            var mutationTarget = mutationsList[0].target,
                mutationTargetConfig,
                changeEvent = new Event('change');

            if (mutationTarget.tagName !== 'SELECT') {
                return;
            }

            mutationTargetConfig = this.currentProductConfig.filter(function (configItem) {
                return configItem.name === mutationTarget.name;
            });

            if (mutationTargetConfig.length === 0) {
                return;
            }

            mutationTarget.value = mutationTargetConfig[0].value;
            mutationTarget.dispatchEvent(changeEvent);
        },

        /**
         * Load product configuration and open modal window
         * @param {Number} productId
         * @returns {void}
         */
        loadConfigurationAndOpenModal: function (productId) {
            if (this.origBlockForm === null) {
                this.origBlockForm = productConfigure.blockFormFields;
                this.origOpenCallback = productConfigure._showWindow;
                this.origConfirmCallback = productConfigure.onConfirmBtn;
            }
            this.currentProductId = productId;

            // Magento composite configure component items
            productConfigure.blockFormFields = this.modal.find('#product_composite_configure_form_fields')[0];
            productConfigure.onConfirmBtn = this.saveConfigurationAndClose.bind(this);
            productConfigure._showWindow = this.openModal.bind(this);

            productConfigure._requestItemConfiguration('product_to_add', productId);
        },

        /**
         * Save product configuration to products listing and close modal
         * @returns {void}
         */
        saveConfigurationAndClose: function () {
            var form = this.modal.find('form'),
                qtyField = form.find('[name="qty"]'),
                qty = qtyField.length ? qtyField.val() : null;

            if (form.valid()) {
                this.configureFormValidState = true;
                this.productsListing().saveConfiguration(this.currentProductId, form.serializeArray());
                this.closeModal();
                this.saveQtyToGrid(+this.currentProductId, +qty);
            }
        },

        closeModal: function () {
            this.restoreConfiguration();
            this._super();

            if (!this.configureFormValidState) {
                this.selectColumn().deselect(this.currentProductId, true);
            }

            this.configureFormValidState = false;
        },

        /**
         * Save product qty from configure popup to products grid
         * @param {Number} productId
         * @param {Number} qty
         * @returns {void}
         */
        saveQtyToGrid: function (productId, qty) {
            quantityInputActions.toggleQtyInput(productId, true);
            quantityInputActions.setQtyValue(productId, qty);
        },

        /**
         * Restore configure window to prevent conflicts with native product addition
         * @returns {void}
         */
        restoreConfiguration: function () {
            productConfigure.blockFormFields = this.origBlockForm;
            productConfigure._showWindow = this.origOpenCallback;
            productConfigure.onConfirmBtn = this.origConfirmCallback;
            this.origBlockForm = null;
            this.origOpenCallback = null;
            this.origConfirmCallback = null;
        }
    });
});
