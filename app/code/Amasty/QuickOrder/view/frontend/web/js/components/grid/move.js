define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            isActive: ko.observable(true),
            buttons: [],
            modules: {
                grid: 'grid',
                pager: 'pager'
            },
        },

        /**
         * @desc Try move items from grid to cart/quote cart.
         * In error - scroll to product with error.
         * In success - redirect to cart/quote cart url.
         *
         * @param {string} url
         */
        moveItems: function (url) {
            var self = this;

            $.ajax({
                url: url,
                showLoader: true,
                data: self.getRequestData(),
                type: self.getRequestType(),
                cache: false,
                dataType: 'json',
                error: function (result) {
                    console.error(result.errors);
                },
                success: function (response) {
                    self.successAction(response);
                }
            });
        },

        getRequestType: function () {
            return 'POST';
        },

        getRequestData: function () {
            return {
                form_key: window.FORM_KEY
            };
        },

        successAction: function (response) {
            var grid = this.grid(),
                pager = this.pager();

            if (response.errors && response.errors[0]) {
                var page = response.errors[0].page,
                    itemId = response.errors[0].item_id,
                    message = response.errors[0].message;

                grid.updateItem(itemId, {'errors': [message]});
                pager.setCurrentPage(page);
                grid.scroll(itemId);
            } else {
                $('body').loader('show');
                grid.clearItems();

                window.location.href = response.redirect;
            }
        },

        /**
         * @desc Check if move button disabled.
         *
         * @return {boolean}
         */
        isDisabled: function () {
            return !this.grid().isCheckoutEnabled() || !this.isActive();
        }
    });
});
