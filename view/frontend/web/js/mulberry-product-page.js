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
    'priceUtils',
], function ($, mulberry, _, utils) {
    'use strict';

    $.widget('mulberry.productPage', {
        productUpdateTimer: null,
        mulberryProductUpdateDelay: 1000,

        _create: function () {
            mulberry.loadLibrary();
            this.initLibrary();
        },

        /**
         * Register events
         */
        addProductListeners: function addProductListeners()
        {
            document.addEventListener('mulberry:warranty-toggle', (e) => {
                this.toggleWarranty(e.detail, e.detail.isSelected);
            });

            this.prepareMulberryProduct();

            this.element.on('updateMulberryProduct', function (evt, newPrice) {
                this.updateMulberryProduct(newPrice);
            }.bind(this));

            this.element.on('toggleWarranty', (evt, params) => {
                this.toggleWarranty(params.data, params.isSelected);
            });
        },

        /**
         * Init Mulberry product
         */
        registerProduct: function registerProduct()
        {
            window.mulberry.init(
                window.mulberryProductData.product,
                window.mulberryConfigData.containerClass,
                window.mulberryConfigData.magentoDomain,
                window.mulberryConfigData.mulberryUrl,
                window.mulberryConfigData.partnerUrl
            );
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
                warrantyElement = $('#warranty');

            if (data) {
                selectedWarrantyHash = isSelected && data.offer ? data.offer.warranty_hash : '';
            }

            warrantyElement.attr('name', 'warranty[' + window.mulberryProductData.product.id + ']');
            warrantyElement.val(selectedWarrantyHash);
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
         * Prepare selected product SKU for Mulberry API
         */
        prepareSimpleSku: function prepareSimpleSku()
        {
            var configurableData = $(this.element).data('mageConfigurable');

            return configurableData ? configurableData.options.spConfig.simple_skus[configurableData.simpleProduct] : $(this.element).data('productSku');
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
            var sku = this.prepareSimpleSku() ? this.prepareSimpleSku() : window.mulberryProductData.originalSku,
                customOptionsSku = this.prepareOptionsSku();

            if (customOptionsSku !== '') {
                sku += customOptionsSku;
            }

            return {
                id: sku,
                title: window.mulberryProductData.product.title,
                price: newPrice ? newPrice : window.mulberryProductData.originalPrice,
                description: window.mulberryProductData.originalDescription
            }
        },

        /**
         * Run Mulberry product update
         *
         * @param newPrice
         */
        updateMulberryProduct: function updateMulberryProduct(newPrice)
        {
            var newConfig = this.prepareMulberryProduct(newPrice);

            if (!window.mulberry || !window.mulberry.mulberryModal || !window.mulberry.mulberryInline) {
                return;
            }

            /**
             * Run update only when product configuration has been changed
             *
             * @type {number}
             */
            clearTimeout(this.productUpdateTimer);
            this.productUpdateTimer = setTimeout(function () {
                if (this.hasConfigurationChanges(newConfig)) {
                    window.mulberry.updateProduct(window.mulberryProductData.product);
                    window.mulberryProductData.product = newConfig;
                    $('#warranty').attr('name', 'warranty[' + window.mulberryProductData.product.id + ']');
                }
            }.bind(this), this.mulberryProductUpdateDelay);
        },

        /**
         * Check, if product has configuration changes and we need to trigger Mulberry product update
         *
         * @param newConfig
         * @returns {boolean}
         */
        hasConfigurationChanges: function hasConfigurationChanges(newConfig)
        {
            var currentConfig = window.mulberryProductData.product;

            return !_.isEqual(currentConfig, newConfig);
        },

        /**
         *
         * @param element
         * @param optionsConfig
         * @private
         */
        _getOptionSku: function getOptionValue(element, optionsConfig)
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
