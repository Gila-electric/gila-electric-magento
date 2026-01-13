define([
    'jquery',
    'Amasty_QuickOrder/js/catalog/quickorder-subtotal'
], function ($, subtotal) {
    'use strict';

    $.widget('mage.amGiftcardAmount', subtotal, {
        options: {
            selectors: {
                amountInput: '.input-text, .giftcard-amount-entry',
                customAmountInput: '.input-text'
            },
            elements: {
            },
        },

        /**
         * Save elements in cache.
         */
        _initialize: function () {
            this._super();
            this.options.elements.amount = this.element.find(this.options.selectors.amountInput);
            this.options.elements.customAmount = this.element.find(this.options.selectors.customAmountInput);
        },

        /**
         * Initialize event listeners.
         */
        _initEventListeners: function () {
            var $widget = this;

            this.options.elements.amount.on('change input', function () {
                return $widget._updatePrice(
                    $widget,
                    $widget._getNewPrices($(this).val())
                );
            });
        },

        /**
         * Action when all price boxes initialized.
         */
        _allPriceBoxesInitialized: function () {
            var newPrices = this._getNewPrices();
            this.mainPriceBox.priceBox(
                'setDefault',
                newPrices
            );
            if (newPrices.quickOrderSubtotal.amount > 0) {
                this._updatePrice(this);
            }
        },

        /**
         * Get info about currently selected prices.
         * @param value
         * @returns {Object}
         * @private
         */
        _getNewPrices: function (value) {
            if (value === 'custom') {
                value = this.options.elements.customAmount.val();
            }

            return {
                'quickOrderSubtotal': {
                    'amount': +value
                }
            }
        }
    });

    return $.mage.amGiftcardAmount;
});
