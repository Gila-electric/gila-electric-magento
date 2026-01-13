/**
 *  Amasty Multiple UI Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/url',
    'mage/translate'
], function ($, Component, ko, urlBuilder, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            baseUrl: window.BASE_URL,
            uploadFileUrl: 'amasty_quickorder/item_import/file',
            uploadListUrl: 'amasty_quickorder/item_import/multipleInput',
            acceptUrl: 'amasty_quickorder/item/moveTemp',
            sampleXmlUrl: 'amasty_quickorder/file_sample/xml',
            sampleCsvUrl: 'amasty_quickorder/file_sample/csv',
            files: ko.observable(false),
            list: ko.observable(),
            fileErrors: ko.observable(false),
            modules: {
                grid: 'grid',
                popup: 'popup'
            }
        },
        wrapper: $('[data-amqorder-js="multiple"]'),
        classes: {
            active: '-active',
            error: '-error'
        },

        /**
         * Initializes component
         */
        initialize: function () {
            this._super();

            this.buildUrls();
        },

        buildUrls: function() {
            urlBuilder.setBaseUrl(this.baseUrl);
            this.uploadFileUrl = urlBuilder.build(this.uploadFileUrl);
            this.uploadListUrl = urlBuilder.build(this.uploadListUrl);
            this.acceptUrl = urlBuilder.build(this.acceptUrl);
            this.sampleXmlUrl = urlBuilder.build(this.sampleXmlUrl);
            this.sampleCsvUrl = urlBuilder.build(this.sampleCsvUrl);
        },

        /**
         * Toggling Aside Block
         *
         */
        toggle: function () {
            this.wrapper.toggleClass(this.classes.active);
        },

        /**
         * Upload files to the server
         *
         */
        uploadFile: function () {
            var self = this,
                grid = self.grid(),
                formData = new FormData();

            self.fileErrors(false);

            $.each(self.files(), function (index, item) {
                formData.append('multiple_file', item);
            });

            $.ajax({
                url: self.uploadFileUrl,
                enctype: 'multipart/form-data',
                type: 'POST',
                processData: false,
                contentType: false,
                showLoader: true,
                data: formData,
                error: function (result) {
                    if (result.status === 413) {
                        self.fileErrors([$t('The file is too large')]);
                    } else {
                        self.fileErrors([result.responseJSON ? result.responseJSON.message : result.statusText]);
                    }
                },
                always: function () {
                    self.files(false);
                },
                success: function (result) {
                    if (Object.keys(result.errors).length) {
                        self._initPopup(result);
                    } else if (result.items.length) {
                        grid.initItems(result.items);
                    } else {
                        self.fileErrors([$t('The file is empty.')]);
                    }
                }
            });
        },

        /**
         * Upload list to the server
         *
         */
        uploadList: function () {
            var self = this,
                grid = self.grid();

            $.ajax({
                url: self.uploadListUrl,
                enctype: 'multipart/form-data',
                type: 'POST',
                showLoader: true,
                data: {
                    'multiple_sku': self.list()
                },
                success: function (result) {
                    self.list('');

                    if (Object.keys(result.errors).length) {
                        self._initPopup(result);
                    } else {
                        grid.initItems(result.items);
                    }
                }
            });
        },

        /**
         * Init error messages list popup method
         *
         */
        _initPopup: function (data) {
            var self = this,
                popup = self.popup(),
                type = 'messages',
                invalidItemsQty = Object.keys(data.errors).length,
                successItemsQty = data.total_qty - invalidItemsQty,
                header = successItemsQty + ' ' + $t('out of') + ' ' + data.total_qty + '</br>' + $t('successfully passed validation'),
                description = $t('Items below will not be added to the grid because:');

            popup.type(type);
            popup.header(header);
            popup.description(description);
            popup.buttons.push({
                'text': $t('Accept'),
                'classes': '-fill -secondary',
                'callback': function () {
                    self._acceptProducts();
                    popup.hide();
                }
            });
            popup.buttons.push({
                'text': $t('Cancel'),
                'classes': '-fill -error',
                'callback': function () {
                    popup.hide();
                }
            });
            $.each(data.errors, function (index, item) {
                popup.messagesList.push(item);
            });
            popup.show();
        },

        /**
         * Uploaded products approve for current session method
         *
         */
        _acceptProducts: function () {
            var self = this,
                grid = self.grid();

            $.ajax({
                url: self.acceptUrl,
                enctype: 'multipart/form-data',
                type: 'POST',
                showLoader: true,
                error: function (result) {
                    console.info(result);
                },
                success: function (result) {
                    grid.initItems(result);
                    self.list('');
                }
            });
        }
    });
});
