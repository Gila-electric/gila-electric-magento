define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore'
], function ($, Component, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            currentPage: ko.observable(1),
            pageSize: 1,
            modules: {
                grid: 'grid'
            }
        },
        isPaginationVisible: ko.observable(false),
        pagesToDisplay: ko.observableArray([]),

        /**
         * Initializes component
         */
        initialize: function () {
            var self = this;

            self._super();

            self.currentPage.subscribe(function (value) {
                self.grid().itemStorage.page(value);
            });
        },

        /**
         * @desc Update items on page; update pages list.
         */
        refresh: function () {
            var firstPositionInPage = (this.currentPage() - 1) * this.pageSize,
                lastPositionInPage = firstPositionInPage + this.pageSize,
                itemsOnPage = this.getAllItems().slice(firstPositionInPage, lastPositionInPage);

            this._updateDisplayedPages();
            this.grid().elems(itemsOnPage);

            this.isPaginationVisible(this.getLastPage() > 1);
        },

        /**
         * @desc Change current page.
         * @param {number} targetPage
         */
        setCurrentPage: function (targetPage) {
            if (targetPage < 1) {
                targetPage = 1;
            } else if (targetPage > this.getLastPage()) {
                targetPage = this.getLastPage();
            }

            this.currentPage(targetPage);
            this.refresh();
            this.grid().itemStorage.page(targetPage);
            this.grid().itemStorage.save();
            $('body').trigger('contentUpdated');
        },

        /**
         * @desc Retrieve last page.
         * @returns {number}
         */
        getLastPage: function () {
            return this._calculatePage(this.getAllItems().length);
        },

        /**
         * @desc Retrieve all items (from all pages).
         * @returns {array}
         */
        getAllItems: function () {
            return this.grid().allItems();
        },

        /**
         * @desc Calculate page for position.
         * @param {number} position
         * @returns {number}
         * @private
         */
        _calculatePage: function (position) {
            var page;

            if (position <= 0) {
                page = 1;
            } else {
                page = Math.ceil(position / this.pageSize);
            }

            return page;
        },

        /**
         * @private
         * @desc Update array with displayed pages
         * 0 - determine element, which render as '...' in pages list
         */
        _updateDisplayedPages: function () {
            var pagesToDisplay = [],
                lastPage = this.getLastPage(),
                maxPagesToDisplay = 5;

            if (lastPage <= maxPagesToDisplay) {
                pagesToDisplay = _.range(1, lastPage + 1);
            } else {
                pagesToDisplay.push(1);
                if (this.currentPage() <= 2) {
                    pagesToDisplay.push(2);
                    pagesToDisplay.push(3);
                } else {
                    pagesToDisplay.push(0);
                }
                if (this.currentPage() >= lastPage - 1) {
                    pagesToDisplay.push(lastPage - 2);
                    pagesToDisplay.push(lastPage - 1);
                } else {
                    if (this.currentPage() > 2) {
                        pagesToDisplay.push(this.currentPage());
                    }
                    if (this.currentPage() < lastPage - 1) {
                        pagesToDisplay.push(0);
                    }
                }
                pagesToDisplay.push(lastPage);
            }

            this.pagesToDisplay(pagesToDisplay);
        },

        /**
         * @desc Get title for element in pages list.
         * @param {number} index
         * @returns {string}
         */
        getPageTitle: function (index) {
            return index ? index : '...';
        },

        /**
         * @desc Detect current page.
         * @param {number} index
         * @returns {boolean}
         */
        isCurrentPage: function (index) {
            return this.currentPage() === index;
        }
    });
});
