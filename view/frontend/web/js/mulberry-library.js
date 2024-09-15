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
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * Register Mulberry library
         *
         * @private
         */
        loadLibrary: function () {
            const scriptSource = window.mulberryConfigData.mulberryUrl + '/plugin/static/js/mulberry.js';

            const pendingLoadScript = Array.from(document.scripts).find(script => script.src === scriptSource);
            if (pendingLoadScript) { // GetMulberry SDK script load is pending being loaded
                return;
            }

            let element = document.createElement('script'),
                scriptTag = document.getElementsByTagName('script')[0];

            element.async = true;
            element.src = scriptSource;
            element.onload = function () {
                require(['mulberry'], function (lib) {
                    window.mulberry = lib;
                });

            };

            document.head.appendChild(element);
        }
    };
});
