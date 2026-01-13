define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            clearUrl: '/amasty_quickorder/category/unselectAll',
            modules: {
                grid: 'grid'
            }
        },

        getCountSelectedItems: function () {
            return this.grid().getCountSelectedItems();
        },

        unselect: function () {
            var self = this;

            $.ajax({
                url: self.clearUrl,
                showLoader: true,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                }
            });
        }
    });
});
