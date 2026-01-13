define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            selectors: {
                checkedControl: '[data-amqorder-js^="checked"]:not(.-disabled, :checked)'
            },
            modules: {
                grid: 'grid'
            }
        },

        getTemplateTitle: function () {
            return this.template_title;
        },

        selectAll: function () {
            $(this.selectors.checkedControl).click();
        },

        isChecked: function (itemId) {
            return !!this.grid().getSectionData(itemId);
        }
    });
});
