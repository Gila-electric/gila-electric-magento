/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/* global $, $H */

define([
    'jquery',
    'mage/adminhtml/grid',
    'jquery/jquery-ui-timepicker-addon'
], function ($) {
    'use strict';

    return function (config) {
        var rewardProducts = $H({}),
            gridJsObject = window[config.gridJsObjectName],
            length = rewardProducts.keys().length;
        $('#wk_productids').val(Object.toJSON(rewardProducts));
        var startTimeTextBox = $('#wk_start_time');
        var endTimeTextBox = $('#wk_end_time');
        $.timepicker.datetimeRange(
            startTimeTextBox,
            endTimeTextBox,
            {
                timeOnly: true,
                timeFormat: 'hh:mm TT',
                controlType: 'select',
                start: {},
                end: {}
            }
        );

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function rewardProductCheckBoxClick(grid, element, checked)
        {
            if (element.className != "admin__control-checkbox") {
                if (checked) {
                    rewardProducts.set(element.value, length+1);
                } else {
                    rewardProducts.unset(element.value);
                }
                length = rewardProducts.keys().length;
                $('#wk_productids').val(Object.toJSON(rewardProducts));
                grid.reloadParams = {
                    'selected_products[]': rewardProducts.keys()
                };
            }
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function rewardProductRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;
            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }
        gridJsObject.rowClickCallback = rewardProductRowClick;
        gridJsObject.checkboxCheckCallback = rewardProductCheckBoxClick;
    };
});
