/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

/* jscs:disable */
/* eslint-disable */
define([
    'jquery',
    'mulberryLibrary',
    'underscore',
    'priceUtils'
], function ($, mulberry, _, utils) {
    'use strict';

    $.widget('mulberry.productPage', {
        options: {
            productUpdateTimer: null,
            mulberryProductUpdateDelay: 1000,
            swatchElement: '[data-role=swatch-options]',
            swatchAttributeElement: 'div.swatch-attribute',
            warrantyHashElement: '#warranty_hash',
            warrantySkuElement: '#warranty_sku',
        },

        _create: function () {
            mulberry.loadLibrary();
            this.initLibrary();
        },

        /**
         * Register events
         */
        addProductListeners: function addProductListeners()
        {
            this.prepareMulberryProduct();

            this.element.on('updateMulberryProduct', function (evt, newPrice) {
                this.updateMulberryProduct(newPrice);
            }.bind(this));

            this.element.on('toggleWarranty', function (evt, params) {
                this.toggleWarranty(params.data, params.isSelected);
            }.bind(this));
        },

        /**
         * Init Mulberry product
         */
        registerProduct: function registerProduct()
        {
            var self = this;

            window.mulberry.core.init({
                publicToken: window.mulberryConfigData.publicToken
            }).then(
                self.registerOffers()
            );
        },

        /**
         * Register inline & modal offers
         */
        registerOffers: function registerOffers() {
            var self = this;

            window.mulberry.core.getWarrantyOffer(window.mulberryProductData.activeSelection)
                .then(function (offers) {
                    if (offers.length) {
                        var settings = window.mulberry.core.settings;

                        if (settings.has_modal) {
                            window.mulberry.modal.init({
                                offers,
                                settings,
                                placement: 'pdp',
                                onWarrantyDecline: function () {
                                    $(self.element).trigger('onWarrantyDecline');
                                },
                                onWarrantySelect: function (warranty) {
                                    $(self.element).trigger('onWarrantySelect', warranty);
                                }
                            });
                        }

                        if (settings.has_inline) {
                            window.mulberry.inline.init({
                                offers: offers,
                                settings: settings,
                                selector: '.mulberry-inline-container',
                                onWarrantyToggle: function(warranty) {
                                    self.toggleWarranty(warranty.offer, warranty.isSelected);
                                }
                            });
                        }
                    }
                });
        },

        /**
         * Update warranty product's hash
         *
         * @param data
         * @param isSelected
         */
        toggleWarranty: function toggleWarranty(data, isSelected)
        {
            var selectedWarrantyHash = '',
                warrantyHashElement = $(this.options.warrantyHashElement),
                warrantySkuElement = $(this.options.warrantySkuElement);

            if (data) {
                selectedWarrantyHash = isSelected && data ? data.warranty_hash : '';
            }

            warrantySkuElement.val(window.mulberryProductData.product.id);
            warrantyHashElement.val(selectedWarrantyHash);
        },

        /**
         * Init Mulberry API library
         */
        initLibrary: function initLibrary()
        {
            if (window.mulberry) {
                this.registerProduct();
                this.addProductListeners();
            } else {
                setTimeout(function () {
                    this.initLibrary()
                }.bind(this), 50);
            }
        },

        /**
         * Retrieve selected simple product SKU if configurable swatches are used
         */
        getSelectedSwatchProduct: function getSelectedSwatchProduct()
        {
            var selectedOptions = {};
            var options = $(this.options.swatchAttributeElement);

            options.each((index, value) => {
                let attributeId = $(value).attr('data-attribute-id');
                let optionSelected = $(value).attr('data-option-selected');

                /**
                 * Return, if there's no selection
                 */
                if (!attributeId || !optionSelected) {
                    return '';
                }

                selectedOptions[attributeId] = optionSelected;
            });

            var swatchConfig = $(this.options.swatchElement).data('mageSwatchRenderer').options.jsonConfig;

            for (var [productId, attributes] of Object.entries(swatchConfig.index)) {
                if (_.isEqual(attributes, selectedOptions)) {
                    return swatchConfig.simple_skus[productId];
                }
            }
        },

        /**
         * Prepare selected product SKU for Mulberry API
         */
        prepareSimpleSku: function prepareSimpleSku()
        {
            /**
             * Retrieve simple SKU, if the Magento swatch attributes are used
             *
             * @type {*|Window.jQuery}
             */
            var swatchData = $(this.options.swatchElement).data('mageSwatchRenderer');

            if (swatchData) {
                var selectedSku = this.getSelectedSwatchProduct();

                if (selectedSku) {
                    return selectedSku;
                }
            }

            /**
             * Retrieve simple SKU, if the default configurable attributes are used
             *
             * @type {*|Window.jQuery}
             */
            var configurableData = $(this.element).data('mageConfigurable');

            if (configurableData) {
                var selectedSku = configurableData.options.spConfig.simple_skus[configurableData.simpleProduct];

                if (selectedSku) {
                    return selectedSku;
                }
            }

            /**
             * Fallback to the default SKU
             */
            return $(this.element).data('productSku');
        },

        /**
         * Prepare selected options SKU for Mulberry API
         */
        prepareOptionsSku: function prepareOptionsSku()
        {
            var customOptionsHandler = $(this.element).data('magePriceOptions'),
                result = '';

            if (!customOptionsHandler) {
                return result;
            }

            var optionElements = $(customOptionsHandler.options.optionsSelector),
                customOptionsConfig = customOptionsHandler.options.optionConfig,
                self = this;

            optionElements.each(function () {
                var optionValue = self._getOptionSku($(this), customOptionsConfig);

                if (optionValue !== '') {
                    result += '-' + optionValue;
                }
            });

            return result;
        },

        /**
         * Prepare selected product variant to be passed to Mulberry API
         */
        prepareMulberryProduct: function prepareMulberryProduct(newPrice)
        {
            var sku = this.prepareSimpleSku() ? this.prepareSimpleSku() : window.mulberryProductData.activeSelection.sku,
                customOptionsSku = this.prepareOptionsSku();

            if (customOptionsSku !== '') {
                sku += customOptionsSku;
            }

            var updatedInformation = {
                id: sku,
                price: newPrice
            }

            return Object.assign(window.mulberryProductData.product, updatedInformation);
        },

        /**
         * Run Mulberry product update
         *
         * @param newPrice
         */
        updateMulberryProduct: function updateMulberryProduct(newPrice)
        {
            this.prepareMulberryProduct(newPrice);
            var settings = window.mulberry.core.settings,
                self = this;

            if (!window.mulberry || (!settings.has_modal && !settings.has_inline)) {
                return;
            }

            /**
             * Run update only when product configuration has been changed
             *
             * @type {number}
             */
            clearTimeout(this.options.productUpdateTimer);
            this.options.productUpdateTimer = setTimeout(function () {
                if (this.hasConfigurationChanges()) {
                    window.mulberry.core.getWarrantyOffer(window.mulberryProductData.activeSelection).then(function (offers) {
                        self.updateOffers();
                    });

                    $(this.options.warrantySkuElement).val(window.mulberryProductData.activeSelection.id);
                }
            }.bind(this), this.options.mulberryProductUpdateDelay);
        },

        /**
         * Check whether Mulberry offers are available or not.
         */
        offersAvailable: function offersAvailable() {
            return window.mulberry?.core?.offers?.length > 0;
        },

        inlineOfferInitialized: function inlineOfferInitialized() {
            return window.mulberry?.inline?.length > 0;
        },

        modalOfferInitialized: function modalOfferInitialized() {
            return window.mulberry?.modal?.length > 0;
        },

        /**
         * Toggle Mulberry offers update function.
         * If inline/modal was not initialized yet - toggle init function
         */
        updateOffers: function updateOffers() {
            var settings = window.mulberry.core.settings,
                offers = window.mulberry.core.offers,
                self = this;

            if (settings.has_modal) {
                if (self.modalOfferInitialized()) {
                    window.mulberry.modal.updateOffer(offers);
                } else {
                    window.mulberry.modal.init({
                        offers,
                        settings,
                        placement: 'pdp',
                        onWarrantyDecline: function () {
                            $(self.element).trigger('onWarrantyDecline');
                        },
                        onWarrantySelect: function (warranty) {
                            $(self.element).trigger('onWarrantySelect', warranty);
                        }
                    });
                }
            }

            if (settings.has_inline) {
                if (self.inlineOfferInitialized()) {
                    window.mulberry.inline.updateOffer(offers);
                } else {
                    window.mulberry.inline.init({
                        offers: offers,
                        settings: settings,
                        selector: '.mulberry-inline-container',
                        onWarrantyToggle: function(warranty) {
                            self.toggleWarranty(warranty.offer, warranty.isSelected);
                        }
                    });
                }

                /**
                 * Reset warranty selection on configuration change
                 */
                window.mulberry.inline.deselectOffer();
                self.toggleWarranty(false, false);
            }
        },

        /**
         * Check, if product has configuration changes and we need to trigger Mulberry product update
         *
         * @returns {boolean}
         */
        hasConfigurationChanges: function hasConfigurationChanges()
        {
            /**
             * Make a copy of the new object rather than variable assignment,
             * this is required to avoid the issue when the currentConfig is updated along with the newConfig value
             * @type {any}
             */
            var newConfig = JSON.parse(JSON.stringify(window.mulberryProductData.product));
            var currentConfig = window.mulberryProductData.activeSelection;

            window.mulberryProductData.activeSelection = newConfig;

            return !_.isEqual(currentConfig, newConfig);
        },

        /**
         *
         * @param element
         * @param optionsConfig
         * @private
         */
        _getOptionSku: function _getOptionSku(element, optionsConfig)
        {
            var optionValue = element.val(),
                optionId = utils.findOptionId(element[0]),
                optionType = element.prop('type'),
                optionConfig = optionsConfig[optionId];

            switch (optionType) {
                case 'text':
                case 'textarea':
                    optionValue = optionValue ? optionConfig.value_sku : '';
                    break;
                case 'radio':
                    optionValue = (element.is(':checked') && optionConfig[optionValue] && optionConfig[optionValue].value_sku) ? optionConfig[optionValue].value_sku : '';
                    break;
                case 'select-one':
                    optionValue = (optionConfig[optionValue] && optionConfig[optionValue].value_sku) ? optionConfig[optionValue].value_sku : '';
                    break;
                case 'select-multiple':
                    optionValue = '';

                    _.each(optionConfig, function (row, optionValueCode) {
                        optionValue += _.contains(optionValue, optionValueCode) && row.value_sku ? row.value_sku : '';
                    });
                    break;
                case 'checkbox':
                    optionValue = element.is(':checked') && optionConfig[optionValue].value_sku ? optionConfig[optionValue].value_sku : '';
                    break;
                case 'file':
                    optionValue = optionValue || element.prop('disabled') ? optionConfig[optionValue].value_sku : '';
                    break;
            }

            return optionValue;
        }
    });

    return $.mulberry.productPage;
});
