/**
 *  Amasty Price box for Quick Order Grid
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'priceBox',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template'
], function ($, priceBox, utils, _, mageTemplate) {
    'use strict';

    $.widget('mage.priceBox', $.mage.priceBox, {
        /**
         * Render price unit block.
         */
        reloadPrice: function reDrawPrices() {
            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                priceTemplate = mageTemplate(this.options.priceTemplate),
                inputElement = this.element.parents('[data-amqorder-js="item"]').find('[data-amqorder-js="input"]'),
                qty = inputElement.val() ? inputElement.val() : 1;

            _.each(this.cache.displayPrices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function (memo, amount) {
                    return memo + amount;
                }, price.amount);

                if (qty > 0) {
                    price.final = price.final * qty;
                }
                price.formatted = utils.formatPrice(price.final, priceFormat);

                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                    data: price
                }));
            }, this);
        },

    });

    return $.mage.priceBox;
});
