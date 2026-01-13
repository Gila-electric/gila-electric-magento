define([
    'jquery',
    'Amasty_QuickOrder/js/catalog/quickorder-subtotal'
], function ($, subtotal) {
    'use strict';

    $.widget('mage.amGroupedOptions', subtotal, {
        options: {
            selectors: {
                optionNode: 'tr',
                qtyInput: '.input-text.qty'
            }
        },

        /**
         * Initialize event listeners.
         */
        _initEventListeners: function () {
            var $widget = this;

            this.element.on('input', $widget.options.selectors.qtyInput, function () {
                return $widget._updatePrice($widget, $widget._getNewPrices());
            });
        },

        /**
         * Action when all price boxes initialized.
         */
        _allPriceBoxesInitialized: function () {
            $(this.element).trigger('grouped.initialized');
        },

        /**
         * Get info about currently selected prices.
         *
         * @returns {Object}
         * @private
         */
        _getNewPrices: function () {
            var $widget = this,
                prices = {};

            $.each($widget.element.find($widget.options.selectors.qtyInput), function (index, qtyInput) {
                $.each($widget._getPriceBox($(qtyInput)).priceBox('option').prices, function (priceCode, price) {
                    if (!prices[priceCode]) {
                        prices[priceCode] = {amount: 0};
                    }
                    prices[priceCode].amount += price.amount * +$(qtyInput).val();
                });
            });

            return prices;
        }
    });

    return $.mage.amGroupedOptions;
});
