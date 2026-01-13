/**
 *  Amasty Search UI Component
 *
 *  @desc Search Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/url',
    'rjsResolver',
], function ($, ko, Component, urlBuilder, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            minChars: 2,
            maxChars: 128,
            baseUrl: window.BASE_URL,
            requestTimeout: 800,
            searchUrl: 'rest/V1/amasty_quickorder/search',
            addUrl: 'amasty_quickorder/item_import/add',
            readyForRequest: true,
            request: null,
            timeOut: null,
            isEmpty: ko.observable(false),
            isEmptyInput: ko.observable(true),
            modules: {
                grid: 'grid'
            }
        },
        selectors: {
            qty: '[data-amqorder-js="input"]',
            search: '[data-amqorder-js="search-wrapper"]',
            input: '[data-amqorder-js="search-input"]'
        },
        nodes: {},

        /**
         * Initializes component
         */
        initialize: function () {
            this._super();

            this.buildUrls();
            resolver(function () {
                this.nodes.wrapper = $(this.selectors.search);
                this.nodes.input = this.nodes.wrapper.find(this.selectors.input);
            }.bind(this));
        },

        buildUrls: function() {
            urlBuilder.setBaseUrl(this.baseUrl);
            this.searchUrl = urlBuilder.build(this.searchUrl);
            this.addUrl = urlBuilder.build(this.addUrl);
        },

        /**
         * Search request method
         *
         * @desc search by sku or product name and set the received parameters in the template
         */
        search: function () {
            var self = this,
                value = self.nodes.input.val();

            self.elems([]);
            self.isEmpty(false);
            clearTimeout(self.timeOut);

            if (self.request) {
                self.request.abort();
            }

            if (value.length) {
                self.isEmptyInput(false);
            } else {
                self.isEmptyInput(true);
            }

            if (value && value.length >= self.minChars && value.length <= self.maxChars) {
                self.timeOut = setTimeout(function () {
                    self.request = $.ajax({
                        url: self.searchUrl,
                        showLoader: true,
                        data: {
                            form_key: window.FORM_KEY,
                            'searchTerm': value
                        },
                        type: 'GET',
                        dataType: 'json',
                        error: function () {
                            self.isEmpty(true);
                        },
                        success: function (item) {
                            self.elems(item);

                            if (!item.length) {
                                self.isEmpty(true);
                            } else {
                                $(self.selectors.search).trigger('contentUpdated');
                            }
                        }
                    });
                }, self.requestTimeout);
            }
        },

        /**
         * Clear method
         *
         * @desc Clearing search input and elems
         */
        clear: function () {
            this.isEmptyInput(true);
            this.isEmpty(false);
            this.nodes.input.val('');
            this.elems([]);
        },

        /**
         * Add request method
         *
         * @desc adding product to the grid through the server
         */
        add: function (elem) {
            var self = this,
                grid = self.grid(),
                $elem = $(elem);

            $elem
                .attr({
                    'qty': $elem.attr('qty') || 1
                })
                .hide();

            $.ajax({
                url: self.addUrl,
                showLoader: true,
                data: {
                    form_key: window.FORM_KEY,
                    'item_data': {
                        'sku': elem.sku,
                        'qty': elem.qty
                    }
                },
                type: 'POST',
                dataType: 'json',
                error: function (result) {
                    console.error(result.errors);
                },
                success: function (result) {
                    // can be added multiple products for grouped product type
                    grid.initItems(result.items);
                }
            });
        },

        /**
         * @desc Check if qty block showed for item product type..
         * @param {array} item
         * @return {boolean}
         */
        isQtyShowed: function (item) {
            return item.type_id !== 'grouped';
        }
    });
});
