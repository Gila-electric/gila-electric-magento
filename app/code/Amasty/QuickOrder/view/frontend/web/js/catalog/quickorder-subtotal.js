/**
 *  Amasty Price box for Quick Order Grid
 *  This is abstract widget, all instances in SUBTOTAL folder.
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.amQuickOrderSubtotal', {
        options: {
            itemId: 0,
            selectors: {
                priceBox: '[data-role="priceBox"]',
                priceBoxForProduct: '[data-price-box="product-id-%d"]'
            }
        },

        _create: function () {
            this._initialize();
            this._initEventListeners();
            this._triggerAfterEvent();
        },

        /**
         * Save elements in cache.
         */
        _initialize: function () {
            this.mainPriceBox = $(this.options.selectors.priceBoxForProduct.replace('%d', this.options.itemId));
        },

        /**
         * Initialize event listeners.
         */
        _initEventListeners: function () {
        },

        /**
         * Trigger event for another scripts.
         * Include check for all affected price boxes already initialized.
         */
        _triggerAfterEvent: function () {
            var self = this,
                allPriceBoxesInitialized = true;
            $.each(self._getAllAffectedPriceBoxes(), function (index, priceBoxNode) {
                if (typeof $.data(priceBoxNode, 'mage-priceBox') === 'undefined') {
                    allPriceBoxesInitialized = false;
                    $(priceBoxNode).on('price-box-initialized', self._triggerAfterEvent.bind(self));
                    return false;
                }
            });
            if (allPriceBoxesInitialized) {
                this._allPriceBoxesInitialized();
            }
        },

        /**
         * Action when all price boxes initialized.
         */
        _allPriceBoxesInitialized: function () {
        },

        /**
         * Handler for update pricebox.
         *
         * @param {Object} $widget
         * @param {Object} newPrices
         */
        _updatePrice: function ($widget, newPrices) {
            this.mainPriceBox.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(
                        newPrices,
                        this.mainPriceBox.priceBox('option').prices
                    )
                }
            );
        },

        /**
         * Get prices
         *
         * @param {Object} newPrices
         * @param {Object} displayPrices
         * @returns {*}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            _.each(newPrices, function (price, code) {
                if (!displayPrices[code]) {
                    displayPrices[code] = {amount: 0};
                }
                displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
            });

            return displayPrices;
        },

        /**
         * Get info about currently selected prices.
         *
         * @returns {Object}
         * @private
         */
        _getNewPrices: function () {
            return {};
        },

        /**
         * Get price box node.
         *
         * @returns {Object}
         * @private
         */
        _getPriceBox: function (qtyInput) {
            return qtyInput.closest(this.options.selectors.optionNode)
                .find(this.options.selectors.priceBox);
        },

        /**
         * Get all price boxes for current item.
         *
         * @returns {Object}
         * @private
         */
        _getAllAffectedPriceBoxes: function () {
            return this.element.find(this.options.selectors.priceBox);
        }
    });

    return $.mage.amQuickOrderSubtotal;
});
