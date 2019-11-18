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
            create = targetModule.prototype._create;

        /**
         * Retrieve warranty hidden warranty element
         *
         * @returns {*|jQuery|HTMLElement}
         */
        targetModule.prototype.getMulberryWarrantyElement = function () {
            return $('#warranty');
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

            window.mulberry.modal.onWarrantySelect = function (warranty) {
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
            };

            window.mulberry.modal.onWarrantyDecline = function () {
                if (!self.isCartButtonDisabled()) {
                    window.mulberry.modal.close();
                    self.element.submit();
                    self.options.mulberryOverlayActive = false;
                }
            };
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
            if (window.mulberry.modal) {
                this.addMulberryListeners();
                this.options.mulberryOverlayActive = false;
            }

            return original();
        });

        /**
         * Register events on form submit
         */
        targetModule.prototype.submitForm = wrapper.wrap(submitForm, function (original) {
            if (this.options.mulberryOverlayActive || !window.mulberry || !window.mulberry.modal || !window.mulberry.inline) {
                return original();
            }

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
