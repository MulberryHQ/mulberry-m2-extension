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
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (targetModule) {
        var submitForm = targetModule.prototype.submitForm,
            create = targetModule.prototype._create,
            warrantyHash = '#warranty_hash';

        /**
         * Retrieve warranty hidden warranty element
         *
         * @returns {*|jQuery|HTMLElement}
         */
        targetModule.prototype.getMulberryWarrantyElement = function () {
            return $(warrantyHash);
        };

        /**
         * Update value of warranty element
         */
        targetModule.prototype.toggleMulberryWarranty = function (data, isSelected) {
            this.element.trigger('toggleWarranty', {data: data, isSelected: isSelected});
        };

        /**
         * Add Mulberry product listeners
         */
        targetModule.prototype.addMulberryListeners = function () {
            var self = this;

            this.element.on('onWarrantySelect', function (evt, warranty) {
                if (!self.isCartButtonDisabled()) {
                    self.toggleMulberryWarranty(warranty, true);

                    window.mulberry.modal.close();
                    self.element.submit();
                    self.options.mulberryOverlayActive = false;

                    /**
                     * Reset value for warranty element
                     */
                    self.getMulberryWarrantyElement().val('');
                }
            }.bind(this));

            this.element.on('onWarrantyDecline', function (evt, params) {
                if (!self.isCartButtonDisabled()) {
                    window.mulberry.modal.close();
                    self.element.submit();
                    self.options.mulberryOverlayActive = false;
                }
            }.bind(this));
        };

        /**
         * Perform check whether add to cart button is active
         *
         * @returns {*|jQuery|boolean}
         */
        targetModule.prototype.isCartButtonDisabled = function () {
            var addToCartButton = $(this.element).find(this.options.addToCartButtonSelector);

            return addToCartButton.hasClass(this.options.addToCartButtonDisabledClass);
        };

        targetModule.prototype._create = wrapper.wrap(create, function (original) {
            var self = this,
                counter = 0,
                initListeners = function() {
                    if (window.mulberry?.modal?.length > 0) {
                      self.addMulberryListeners();
                      self.options.mulberryOverlayActive = false;
                    } else {
                        /**
                         * Fix for slow connections.
                         */
                        if (counter <= 20) {
                            counter++;
                            setTimeout(function() {
                                initListeners();
                            }.bind(this), 500);
                        }
                    }
                };

            initListeners();

            return original();
        });

        /**
         * Register events on form submit
         */
        targetModule.prototype.submitForm = wrapper.wrap(submitForm, function (original) {
            /**
             * if Mulberry doesn't exist - skip
             */
            if (this.options.mulberryOverlayActive || !window.mulberry) {
                return original();
            }

            var offersAvailable = window.mulberry?.core?.offers?.length > 0,
                hasModal = window.mulberry?.core?.settings?.has_modal,
                modalOffersInitialized = window.mulberry?.modal?.length > 0;

            /**
             * If Mulberry modal setting is disabled - skip
             */
            if (!hasModal) {
                return original();
            }

            /**
             * If there are no Mulberry offers for the current selection - skip
             */
            if (!offersAvailable || !modalOffersInitialized) {
                return original();
            }

            /**
             * Toggle modal if the warranty is not selected
             */
            if (this.getMulberryWarrantyElement().val() === '') {
                window.mulberry.modal.open();
                this.options.mulberryOverlayActive = true;
            } else {
                return original();
            }
        });

        return targetModule;
    };
});
