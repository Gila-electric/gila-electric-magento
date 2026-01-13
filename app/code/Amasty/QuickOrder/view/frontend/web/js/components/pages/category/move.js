define([
    'jquery',
    'amqorderMove'
], function ($, move) {
    'use strict';

    return move.extend({
        defaults: {
            add_mode: 'from_category'
        },

        getRequestData: function () {
            return Object.assign(this._super(), {
                'add_mode': this.add_mode
            });
        },

        successAction: function (response) {
            if (response.action === 'amasty_quote/cart') {
                delete response.redirect;
            }

            if (response.redirect) {
                window.location.href = response.redirect;
            }
        }
    });
});
