/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

/* jscs:disable */
/* eslint-disable */
define([
    'jquery',
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.priceBox', widget, {
            updatePrice: function updatePrice(newPrices)
            {
                this._super(newPrices);

                $('#product_addtocart_form').trigger('updateMulberryProduct', [this.cache.displayPrices.finalPrice.final]);
            },
        });

        return $.mage.priceBox;
    };
});
