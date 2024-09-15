define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'mulberryLibrary'
], function ($, $t, alert, customerData, mulberry) {
    'use strict';

    $.widget('mage.mulberryCartItem', {
        options: {
            reloadOnSuccess: false,
            itemId: null,
            addToCartUrl: null,
            containerId: null,
            cartContainerPrefix: 'mulberry-cart-container-',
            product: {}
        },

        _create: function () {
            mulberry.loadLibrary();
            this._initLibrary();
        },

        _addToCart: function (warranty, forceReload) {
            if (!warranty) {
                return;
            }

            const isCartPage = window.location.pathname.includes('checkout/cart');

            $.ajax({
                url: this.options.addToCartUrl,
                data: {
                    item_id: this.options.itemId,
                    warranty: warranty,
                    force_page_reload: isCartPage || forceReload, // Force reload if we are on the cart page
                    form_key: $.mage.cookies.get('form_key')
                },
                type: 'post',
                dataType: 'json',
                context: this,

                /** @inheritdoc */
                beforeSend: function () {
                    $(document.body).trigger('processStart');
                },

                /** @inheritdoc */
                complete: function () {
                    $(document.body).trigger('processStop');
                }
            }).done(function (response) {
                if (response.status) {
                    this._handleSuccess(response);
                } else {
                    this._handleError(response.error);
                }
            }).fail(function (xhr, status, error) {
                console.error(error);

                this._handleError($t('Sorry, there has been an error processing your request. Please try again or contact our support.'));
            });
        },

        /**
         * Handle successful response
         *
         * @param response
         * @private
         */
        _handleSuccess: function (response) {
            if (response.force_page_reload) {
                window.location.reload(false);
            }

            customerData.reload(['cart'], false);
        },

        /**
         * Handle error response
         *
         * @param errorMessage
         * @private
         */
        _handleError: function (errorMessage) {
            alert({
                content: errorMessage
            });

            window.mulberry.modal.close();
        },

        /**
         * Init Mulberry SDK & offers
         *
         * @private
         */
        _initLibrary: function () {
            if (window.mulberry) {
                window.mulberry.core.init({
                    publicToken: window.mulberryConfigData.publicToken
                }).then(
                    this.initCartItemOffers()
                );
            } else {
                setTimeout(function () {
                    this._initLibrary()
                }.bind(this), 50);
            }
        },

        /**
         * Init Mulberry API library
         */
        initCartItemOffers: async function () {
            const offers = await window.mulberry.core.getWarrantyOffer(this.options.product);

            await this._initModalContainer(offers);
        },

        /**
         * Init modal offers
         *
         * @param offers
         * @returns {Promise<void>}
         */
        _initModalContainer: async function (offers) {
            let settings = window.mulberry.core.settings,
                self = this;

            const cartItemContainer = '#' + this.options.cartContainerPrefix + this.options.itemId;
            if ($(cartItemContainer).length > 0) {
                await window.mulberry.modalTrigger.init({
                    placement: 'cart',
                    offers: offers,
                    settings,
                    trigger: cartItemContainer,
                    onWarrantyDecline: () => {
                        window.mulberry.modal.close();
                    },
                    onWarrantySelect: (warranty) => self._addToCart(warranty, true),
                });
            }
        },
    });

    return $.mage.cartItemWarranty;
});
