/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Dealership
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';

    $.widget('mage.referralpoints', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            chartDivId: "curve_chart"
        },

        _create: function () {
            this._super();
            var self = this;

            /**
             * js code to draw chart
             * Reference: https://developers.google.com/chart/interactive/docs/gallery/linechart#curving-the-lines
             */
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable(self.options.referralInfo);

                var options = {
                    title: '',
                    curveType: 'function',
                    legend: { position: 'none' },
                    colors: ['#8DC33A', '#FF7A1C']
                };

                var chart = new google.visualization.LineChart(document.getElementById(self.options.chartDivId));

                chart.draw(data, options);
            }

            /**
             * js code to copy referral link on click copy button
             */
            $('button.copylink').click(function(){
                $('#referral-link').select();
                document.execCommand('copy');
            });
        },
    });

    return $.mage.referralpoints;
});
