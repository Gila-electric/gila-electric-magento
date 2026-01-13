/**
 *  Amasty Product Grid UI Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'rjsResolver',
    'mage/url',
    'mage/translate',
    'underscore'
], function ($, Component, ko, resolver, urlBuilder, $t, _) {
    'use strict';

    return Component.extend({
        defaults: {
            initPreselect: false,
            requestTimeout: 1000,
            scrollSpeed: 800,
            updateTimeOut: [],
            updateRequest: {},
            updateRequestLoader: false,
            updItemParams: ['price', 'bundle_option', 'bundle_option_qty', 'super_attribute', 'options', 'options_html', 'super_group'],
            itemSelector: '[data-amqorder-js="item"][data-item-id="%s"]',
            removeUrl: urlBuilder.build('/amasty_quickorder/item/remove'),
            updateUrl: urlBuilder.build('/amasty_quickorder/item/update'),
            clearUrl: urlBuilder.build('/amasty_quickorder/item/removeAll'),
            validateUrl: urlBuilder.build('/amasty_quickorder/item/getValidateData'),
            getAllUrl: urlBuilder.build('/amasty_quickorder/item/getAll'),
            exportUrl: urlBuilder.build('/amasty_quickorder/item/getAll'),
            allItems: [],
            exportEnabled: false,
            itemStorage: {
                page: ko.observable(1),
                isEmpty: ko.observable(true),
                init: function () {
                    var storage = this.get();

                    this.data = {};
                    if (storage) {
                        this.page(storage.page);
                        this.isEmpty(storage.isEmpty);
                        this.items = storage.items;
                        this.data = storage.data ? storage.data : {};
                    }
                },
                get: function () {
                    return JSON.parse(localStorage.getItem('amasty_quickorder_storage'));
                },
                setData: function ($key, $value) {
                    this.data[$key] = $value;
                    return this;
                },
                getData: function ($key) {
                    return this.data[$key];
                },
                save: function (items) {
                    var data = {
                        'isEmpty': items && items.length ? ko.observable(false) : this.isEmpty,
                        'items': items ? _.clone(items).reverse() : this.items,
                        'page': this.page,
                        'data': this.data
                    };

                    localStorage.setItem('amasty_quickorder_storage', ko.toJSON(data));
                },
            },
            errors: {
                qty: 0,
                remove: function (id) {
                    this.qty--;
                    delete this[id];
                },
                init: function (item) {
                    this.qty++;
                    this[item.id] = {
                        'id': item.id,
                        'errors': item.errors
                    };
                },
                getLast: function () {
                    return this[Object.keys(this)[this.qty - 1]];
                }
            },
            modules: {
                popup: 'popup',
                pager: 'pager',
                moveButtons: 'move_buttons'
            }
        },
        selectors: {
            wrapper: '[data-amqorder-js="grid"]',
            groupedOption: '[data-selector="super_group[%d]"]',
            optionsForm: '[data-amqorder-js="form-%d"]',
            mageError: '.mage-error[generated="true"]'
        },

        /**
         * Initializes component
         */
        initialize: function () {
            var self = this;

            self._super();

            self.element = $(self.selectors.wrapper);

            self.allItems = ko.observableArray(self.allItems);

            self.triggerUpdate = _.debounce(function () {
                $('body').trigger('contentUpdated');
            }, self.requestTimeout / 2);

            resolver(function () {
                self.itemStorage.init();
                self._loadData();
                self.itemStorage.setData('storeCheck', self.storeCheck);
            }, self.element[0]);
        },

        /**
         * Load Items From Local Storage
         */
        _loadData: function () {
            if (!this.itemStorage.isEmpty()) {
                this.initItems(this.itemStorage.items);
                this.pager().setCurrentPage(this.itemStorage.page());
                this.validateLoadedItems();
            }
        },

        /**
         * Check if count of items is the save on front and back
         */
        validateLoadedItems: function () {
            var self = this,
                storageStoreCheck = self.itemStorage.getData('storeCheck');

            if (storageStoreCheck && storageStoreCheck !== self.storeCheck) {
                this.reloadAllItems();
                return;
            }

            $.ajax({
                url: self.validateUrl,
                data: {
                    form_key: window.FORM_KEY
                },
                showLoader: false,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.items_count
                        && result.items_count !== self.allItems().length
                    ) {
                        self.reloadAllItems();
                    }
                }
            });
        },

        /**
         * Reload all items from server
         */
        reloadAllItems: function () {
            var self = this;

            $.ajax({
                url: self.getAllUrl,
                showLoader: true,
                data: {
                    form_key: window.FORM_KEY
                },
                type: 'POST',
                dataType: 'json',
                success: function (items) {
                    if (items) {
                        self.allItems([]);
                        self.initItems(items);
                    }
                }
            });
        },

        /**
         * Scroll to target item method
         *
         * @param {object} id - target element id
         */
        scroll: function (id) {
            var self = this,
                selector = self.itemSelector.replace('%s', id),
                target = self.element.find(selector);

            if (target.length) {
                $([document.documentElement, document.body]).animate({
                    scrollTop: target.offset().top - 20
                }, self.scrollSpeed);
            } else {
                console.info('Item to scroll was not found');
            }
        },

        /**
         * Remove request method
         *
         * @param {object} item - target element
         * @desc removing product from the grid by id
         */
        remove: function (item) {
            var self = this;

            $.ajax({
                url: self.removeUrl,
                showLoader: true,
                data: {
                    form_key: window.FORM_KEY,
                    'item_id': item.id
                },
                type: 'POST',
                dataType: 'json',
                success: function () {
                    self.allItems.splice(self.getItemIndex(item.id), 1);
                    self.initItems([]);
                }
            });
        },

        /**
         * Clear Items list method
         *
         * @desc removing all products from current list
         */
        clear: function () {
            var self = this,
                popup = self.popup(),
                type = 'prompt',
                header = $t('Are you sure?');

            popup.type(type);
            popup.header(header);
            popup.buttons.push({
                'text': $t('Not sure'),
                'classes': '-fill -secondary',
                'callback': function () {
                    popup.hide();
                }
            });
            popup.buttons.push({
                'text': $t('Yes'),
                'classes': '-fill -error',
                'callback': function () {
                    $.ajax({
                        url: self.clearUrl,
                        showLoader: true,
                        data: {
                            form_key: window.FORM_KEY
                        },
                        type: 'POST',
                        dataType: 'json',
                        success: function () {
                            self.clearItems();
                        }
                    });
                    popup.hide();
                }
            });

            popup.show();
        },

        /**
         * @desc clear all
         */
        clearItems: function () {
            this.allItems([]);
            this.elems([]);
            this.pager().currentPage(1);
            this.itemStorage.save([]);
        },

        /**
         * @desc find item index in allItems array by item id
         * @param {number} itemId - target element index in array of elems
         * @returns {number}
         */
        getItemIndex: function (itemId) {
            return _.findIndex(this.allItems(), function (item) {
                return item.id === itemId;
            });
        },

        /**
         * @desc find item in allItems array by item id
         * @param {number} itemId - target element index in array of elems
         * @returns {object}
         */
        getItem: function (itemId) {
            return this.allItems()[this.getItemIndex(itemId)];
        },

        /**
         * Update Target Item Method
         *
         * @param {object} item
         * @param {object} additionalData
         * @desc update target Item on the backend
         */
        updateElem: function (item, additionalData) {
            var self = this;

            if (this.initPreselect && typeof additionalData === 'undefined') {
                this.initPreselect = false;

                return;
            }

            if (!self._validateItem(item, additionalData)) {
                return;
            }

            if (typeof additionalData === 'undefined') {
                additionalData = {};
            }

            self.updateTimeOut[item.id] = setTimeout(function () {
                var selector = self.itemSelector.replace('%s', item.id),
                    element = $(selector),
                    itemData = element.find('select, textarea, input').serialize();

                itemData += '&sku=' + item.sku;

                $.each(additionalData, function (name, value) {
                    itemData += '&' + name + '=' + value;
                });

                self.updateRequest[item.id] = $.ajax({
                    url: self.updateUrl,
                    showLoader: false,
                    data: {
                        form_key: window.FORM_KEY,
                        'item_id': item.id,
                        'item_data': itemData
                    },
                    type: 'POST',
                    dataType: 'json',
                    success: function (result) {
                        if (result.reloadItems) {
                            self.reloadAllItems();
                        }

                        if (result.result && result.result[0]) {
                            var item = result.result[0],
                                optionIndex = self.getItemIndex(item.id);

                            $.each(self.updItemParams, function (index, itemOption) {
                                if (item[itemOption]) {
                                    self.allItems()[optionIndex][itemOption] = item[itemOption];
                                }
                            });

                            self.allItems()[optionIndex].errors(item.errors);
                            self.allItems()[optionIndex].optionsHtml = ko.observable(item.options_html);
                            self.itemStorage.save(self.allItems());
                        }

                        self.moveButtons().isActive(true);
                    },
                    complete: function () {
                        delete self.updateRequest[item.id];
                        self.checkQueue();
                    }
                });
            }, self.requestTimeout);
        },

        /**
         * Validate Target Item Method
         *
         * @param {object} item
         * @param {object} additionalData
         * @desc validating target Item on the front
         */
        _validateItem: function (item, additionalData) {
            var form = this.element.find(this.selectors.optionsForm.replace('%d', item.id)),
                errorElems,
                errorElemsInContainer;

            this.moveButtons().isActive(false);
            clearTimeout(this.updateTimeOut[item.id]);

            if (this.updateRequest[item.id]) {
                this.updateRequest[item.id].abort();
            }

            if (additionalData) {
                if (additionalData.checked) {
                    form.validation().valid();
                    errorElems = form.find(this.selectors.mageError);
                    $.each(errorElems, function (key, errorElem) {
                        errorElemsInContainer = $(errorElem).parent().find(this.selectors.mageError);
                        if (errorElemsInContainer.length > 1) {
                            errorElemsInContainer.slice(1).remove();
                        }
                    }.bind(this));
                } else {
                    form.validation();
                    form.data('validator').resetForm();
                }
            }

            if (!this.checkQueue()) {
                return;
            }

            return true;
        },

        /**
         * Check count requests for update in queue.
         * If more than MAX - start loader, else stop loader
         *
         * @return boolean
         */
        checkQueue: function () {
            if (Object.keys(this.updateRequest).length <= 3) {
                $('body').loader('hide');
                this.updateRequestLoader = false;
            } else if (!this.updateRequestLoader) {
                $('body').loader('show');
                this.updateRequestLoader = true;
            }

            return !this.updateRequestLoader;
        },

        /**
         * Grid Items init
         *
         * @param {object} items
         * @desc create grid item structure
         */
        initItems: function (items) {
            var self = this;

            $.each(items, function (index, item) {
                self.initItem(item, 0);
            });

            if (this.pager()) {
                self.pager().refresh();
            }
            self.itemStorage.save(self.allItems());
            self.triggerUpdate();
        },

        /**
         * Grid Item init
         *
         * @param {object} item
         * @param {number} index
         * @desc create grid item structure
         */
        initItem: function (item, index) {
            var self = this,
                shouldRemove = self.getItemIndex(item.id) >= 0 ? 1 : 0;

            index = index ? index : 0;

            item.index = ko.observable(index);
            item.price = ko.observable(item.price);
            item.errors = ko.observable(item.errors);
            item.optionsVisible = ko.observable(true);
            item.qty = ko.observable(item.qty);
            item.optionsQty = ko.observable(item.options_count);
            item.optionsHtml = ko.observable(item.options_html);
            item.checked = ko.observable(item.checked);
            item.available_qty = ko.observable(item.available_qty);

            if (item.errors.length) {
                self.errors.init(item);
            } else {
                self.errors.remove(item.id);
            }

            item.qty.subscribe(function () {
                self.element.find('[data-role="priceBox"]').trigger('reloadPrice');
                self.updateElem(item);
            });

            self.allItems.splice(index, shouldRemove, item);
        },

        /**
         * @desc Merge item data with new itemInfo.
         * @param {number} itemId
         * @param {object} itemInfo
         */
        updateItem: function (itemId, itemInfo) {
            var item = this.getItem(itemId);

            $.each(itemInfo, function (key, data) {
                if (typeof item[key] === 'function') {
                    item[key](data);
                } else {
                    item[key] = data;
                }
            });
        },

        /**
         * @desc preselect swatch options.
         * @param {array} item
         * @param {array} options
         */
        preselectConfigurable: function (item, options) {
            var self = this;

            if (!item.super_attribute) {
                return
            }

            $.each(item.super_attribute, function (attrId, optionId) {
                var attribute = $(options).find('.swatch-attribute[attribute-id="' + attrId + '"],'
                    + '.swatch-attribute[data-attribute-id="' + attrId + '"]'),
                    option = attribute.find('[option-id="' + optionId + '"], [data-option-id="' + optionId + '"]');
                if (!option.hasClass('selected')) {
                    self.initPreselect = true;
                    option.attr({
                        'selected': 'selected'
                    }).click();
                }
            });
        },

        /**
         * @desc preselect configurable options.
         * @param {array} item
         * @param {array} options
         */
        preselectConfigurableDropDowns: function (item) {
            if (!item.super_attribute) {
                return
            }

            var selector = this.itemSelector.replace('%s', item.id),
                element = $(selector),
                configurableWidget = element.data('mageConfigurable');

            if (!configurableWidget) {
                return;
            }
            configurableWidget.options.values = item.super_attribute || {};
            configurableWidget._configureForValues();
        },

        /**
         * @desc preselect grouped options.
         * @param {array} item
         */
        preselectGrouped: function (item) {
            var self = this,
                itemNode = $(self.itemSelector.replace('%s', item.id)),
                options = item.super_group || {};

            $.each(options, function (childId, childQty) {
                itemNode.find(self.selectors.groupedOption.replace('%d', childId))
                    .val(childQty)
                    .trigger('input');
            });
        },

        /**
         * @desc Check if move button disabled.
         *
         * @return {boolean}
         */
        isCheckoutEnabled: function () {
            return this.isGridHasItems();
        },

        /**
         * @desc Check if grid has items.
         *
         * @return {boolean}
         */
        isGridHasItems: function () {
            return !!this.allItems().length;
        },

        /**
         * @desc Get available qty for item.
         * @param {array} item
         * @return {number}
         */
        getAvailableQty: function (item) {
            return item.available_qty() ? item.available_qty() : null;
        },

        /**
         * @desc Get stock status for item.
         * @param {array} item
         * @return {boolean}
         */
        getStockStatus: function (item) {
            return !!item.stock_status;
        },

        /**
         * @desc Get errors for item.
         * @param {array} item
         * @return {boolean}
         */
        getErrors: function (item) {
            return item.errors();
        },

        /**
         * @desc Check if qty block showed for item product type..
         * @param {array} item
         * @return {boolean}
         */
        isQtyShowed: function (item) {
            return item.type_id !== 'grouped';
        },

        downloadList: function () {
            location.href = this.exportUrl;
        }
    });
});
