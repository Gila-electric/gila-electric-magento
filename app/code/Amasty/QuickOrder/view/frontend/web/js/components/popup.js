/**
 *  Amasty Popup UI Component
 *
 *  @desc Popup Component Quick Order Module
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'ko',
    'uiComponent',
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            isActive: ko.observable(false),
            header: ko.observable(false),
            content: ko.observable(false),
            description: ko.observable(false),
            messagesList: ko.observableArray(),
            buttons: ko.observableArray(),
            type: ko.observable(false)
        },
        classes: {
            active: '-active',
            openPopup: '-popup-opened'
        },
        wrapper: $('[data-amqorder-js="popup"]'),

        /**
         * Amasty Popup Ui Component Init
         *
         */
        initialize: function () {
            var self = this;

            self._super();

            self.wrapper.click(function (event) {
                if (self.wrapper.is(event.target)) {
                    self.hide();
                }
            });
        },

        /**
         * Amasty Popup Show method
         *
         */
        show: function () {
            var self = this;

            self.wrapper.addClass(self.classes.active);
            $('body').addClass(self.classes.openPopup);
        },

        /**
         * Amasty Popup Hide method
         *
         */
        hide: function () {
            var self = this;

            self._clear();
            self.wrapper.removeClass(self.classes.active);
            $('body').removeClass(self.classes.openPopup);
        },

        /**
         * Amasty Popup Clear method
         *
         */
        _clear: function () {
            var self = this;

            self.header(false);
            self.description(false);
            self.messagesList([]);
            self.buttons([]);
            self.type(false);
        },
    });
});
