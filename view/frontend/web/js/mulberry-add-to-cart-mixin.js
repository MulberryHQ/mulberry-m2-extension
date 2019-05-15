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
            document.addEventListener('mulberry:add-warranty', (e) => {
                this.toggleMulberryWarranty(e.detail, true);

                if (!this.isCartButtonDisabled()) {
                    window.mbModal.close();
                    this.element.submit();
                    this.options.mulberryOverlayActive = false;
                }
            });

            document.addEventListener('mulberry:add-product', (e) => {
                if (!this.isCartButtonDisabled()) {
                    window.mbModal.close();
                    this.element.submit();
                    this.options.mulberryOverlayActive = false;
                }
            });
        };

        targetModule.prototype.isCartButtonDisabled = function () {
            var addToCartButton = $(this.element).find(this.options.addToCartButtonSelector);

            return addToCartButton.hasClass(this.options.addToCartButtonDisabledClass);
        };

        targetModule.prototype._create = wrapper.wrap(create, function (original) {
            if (window.mulberry) {
                this.addMulberryListeners();
                this.options.mulberryOverlayActive = false;
            }

            return original();
        });

        targetModule.prototype.submitForm = wrapper.wrap(submitForm, function (original) {
            if (this.options.mulberryOverlayActive || !window.mulberry || !window.mbModal || !window.mbInline) {
                return original();
            }

            if (this.getMulberryWarrantyElement().val() === '') {
                window.mbModal.open();
                this.options.mulberryOverlayActive = true;
            } else {
                return original();
            }
        });

        return targetModule;
    };
});
