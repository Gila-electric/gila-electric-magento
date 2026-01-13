define([
    'Magento_Checkout/js/model/totals'
], function (totals) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                template: 'Ignitix_ShippingDiscounts/summary/shipping'
            },

            hasIgnitixShippingDiscount: function () {
                var seg = totals.getSegment('ignitix_shipping_discount');
                return !!(seg && typeof seg.value !== 'undefined' && parseFloat(seg.value) < 0);
            },

            getIgnitixOriginalShippingValue: function () {
                if (!this.hasIgnitixShippingDiscount()) {
                    return '';
                }

                var shipSeg = totals.getSegment('shipping');
                var discSeg = totals.getSegment('ignitix_shipping_discount');

                if (!shipSeg || typeof shipSeg.value === 'undefined' || !discSeg || typeof discSeg.value === 'undefined') {
                    return '';
                }

                var current  = parseFloat(shipSeg.value);   // discounted shipping
                var discount = parseFloat(discSeg.value);   // negative
                var original = current - discount;          // current + abs(discount)

                // IMPORTANT: use Magento component formatter (locale-aware)
                return this.getFormattedPrice(original);
            },

            getIgnitixShippingDiscountValue: function () {
                if (!this.hasIgnitixShippingDiscount()) {
                    return '';
                }

                var discSeg = totals.getSegment('ignitix_shipping_discount');
                if (!discSeg || typeof discSeg.value === 'undefined') {
                    return '';
                }

                // discSeg.value is already negative
                return this.getFormattedPrice(parseFloat(discSeg.value));
            }
        });
    };
});