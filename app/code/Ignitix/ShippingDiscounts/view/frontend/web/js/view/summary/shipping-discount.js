define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/totals'
], function (Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ignitix_ShippingDiscounts/summary/shipping-discount',
            title: ''
        },

        isDisplayed: function () {
            return this.getPureValue() !== 0;
        },

        getPureValue: function () {
            var segment = totals.getSegment('ignitix_shipping_discount');
            if (!segment || typeof segment.value === 'undefined') {
                return 0;
            }
            return parseFloat(segment.value);
        },

        getValue: function () {
            // abstract-total already provides getFormattedPrice(value)
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});