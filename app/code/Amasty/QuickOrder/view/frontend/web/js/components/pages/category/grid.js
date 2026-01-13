/**
 *  Amasty Category Product Grid UI Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'underscore',
    'ko',
    'mage/url',
    'amqorderGrid',
    'Magento_Customer/js/customer-data'
], function ($, _, ko, urlBuilder, grid, customerData) {
    'use strict';

    return grid.extend({
        defaults: {
            updateUrl: urlBuilder.build('/amasty_quickorder/category/updateItem'),
            loadOptionsUrl: urlBuilder.build('/amasty_quickorder/category/getOptions'),
            itemStorage: {
                items: {},
                init: function () {
                },
                setData: function () {
                },
                getData: function () {
                },
                save: function () {
                }
            },
            selectors: {
                checkedControl: '[data-amqorder-js="checked-%d"]',
                wrapper: '[data-amqorder-js="grid"]',
                groupedOption: '[data-selector="super_group[%d]"]',
                optionsForm: '[data-amqorder-js="form-%d"]',
                mageError: '.mage-error[generated="true"]'
            }
        },

        /**
         * Load Items From Local itemStorage
         */
        _loadData: function () {
            this.initItems(this.itemStorage.items.reverse());
            this.elems(this.allItems());
            this.triggerUpdate();
        },

        /**
         * Grid Items init
         *
         * @param {object} items
         * @desc create grid item structure
         */
        initItems: function (items) {
            this._super(items);
            this._reloadOptions(items);
        },

        /**
         * Detect if saved item has preselected options.
         *
         * @param {object} item
         * @return {boolean}
         */
        _isSomeOptionSelected: function (item) {
            var sectionItem = this.getSectionData(item.id);

            if (!sectionItem) {
                return false;
            }

            return sectionItem.hasOwnProperty('super_attribute');
        },

        /**
         * Detect AND Reload items with options.
         *
         * @param {object} items
         */
        _reloadOptions: function (items) {
            var self = this,
                itemsWithOptions = {};

            $.each(items, function (index, item) {
                if (item.optionsQty() > 0 && self._isSomeOptionSelected(item)) {
                    itemsWithOptions['product_ids[]'] = item.id;
                }
            });

            if (!_.isEmpty(itemsWithOptions)) {
                $.ajax({
                    url: self.loadOptionsUrl,
                    data: itemsWithOptions,
                    showLoader: false,
                    type: 'GET',
                    dataType: 'json',
                    success: function (result) {
                        $.each(result, function (itemId, optionsHtml) {
                            self.getItem(+itemId).optionsHtml(optionsHtml);
                        });

                        self.triggerUpdate();
                    }
                });
            }
        },

        /**
         * Grid Item init
         * Resolve unique for customer data from customerData section quickorder_category.
         *
         * @param {object} item
         * @param {number} index
         */
        initItem: function (item, index) {
            this._super(item, index);

            if (this.getSectionData(item.id)) {
                var sectionData = this.getSectionData(item.id);

                item.checked(sectionData.checked);
                item.qty(sectionData.qty);
                item.errors(sectionData.errors);
            }
        },

        /**
         * Change checked flag value.
         */
        checkItem: function (item) {
            if (this.getStockStatus(item)) {
                this.updateElem(item, {
                    checked: +this.isItemChecked(item.id)
                }, true);
            }
        },

        /**
         * Update element depends on item checked flag.
         */
        updateElem: function (item, additionalData, force) {
            if (this.isItemChecked(item.id) || force) {
                if (typeof additionalData === 'undefined') {
                    additionalData = {
                        checked: +this.isItemChecked(item.id)
                    };
                }
                this._super(item, additionalData);
            }
        },

        /**
         * Detect is item checked.
         *
         * @return {boolean}
         */
        isItemChecked: function (itemId) {
            return $(this.selectors.checkedControl.replace('%d', itemId)).is(':checked');
        },

        /**
         * @desc preselect swatch options.
         * @param {array} item
         * @param {array} options
         */
        preselectConfigurable: function (item, options) {
            if (this.getSectionData(item.id)) {
                this._super(this.getSectionData(item.id), options);
            }
        },

        /**
         * @desc preselect configurable options.
         * @param {array} item
         */
        preselectConfigurableDropDowns: function (item) {
            if (this.getSectionData(item.id)) {
                this._super(this.getSectionData(item.id));
            }
        },

        /**
         * @desc preselect grouped options.
         * @param {array} item
         */
        preselectGrouped: function (item) {
            if (this.getSectionData(item.id)) {
                this._super(this.getSectionData(item.id));
            }
        },

        /**
         * @desc Retirn count items from section.
         * @return {number}
         */
        getCountSelectedItems: function () {
            var countSelectedItems = Object.keys(this.getSection()).length - 1;

            return countSelectedItems >= 0 ? countSelectedItems : 0;
        },

        /**
         * Get info about quickorder_category section.
         *
         * @return {object}
         */
        getSection: function () {
            return customerData.get('quickorder_category')();
        },

        /**
         * Get info about item from quickorder_category section.
         *
         * @param {number} itemId
         * @return {object}
         */
        getSectionData: function (itemId) {
            var section = this.getSection();

            return section[itemId];
        },

        /**
         * @desc Check if move button disabled.
         *
         * @return {boolean}
         */
        isCheckoutEnabled: function () {
            return !!this.getCountSelectedItems();
        },

        /**
         * @desc Get available qty for item.
         * @param {array} item
         * @return {number}
         */
        getAvailableQty: function (item) {
            return this.getSectionData(item.id)
                ? this.getSectionData(item.id).available_qty
                : this._super(item);
        },

        /**
         * @desc Get stock status for item.
         * @param {array} item
         * @return {boolean}
         */
        getStockStatus: function (item) {
            return this.getSectionData(item.id)
                ? this.getSectionData(item.id).stock_status
                : this._super(item);
        },

        /**
         * @desc Get errors for item.
         * @param {array} item
         * @return {boolean}
         */
        getErrors: function (item) {
            var errors = this.getSectionData(item.id)
                ? this.getSectionData(item.id).errors
                : [];

            return _.isArray(errors) ? errors : [];
        }
    });
});
