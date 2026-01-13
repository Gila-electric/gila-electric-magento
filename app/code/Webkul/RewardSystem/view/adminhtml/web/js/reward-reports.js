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

    $.widget('mage.rewardpoints', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            chartDivId: "chart_div",
            buttonDivId: "reports_refresh_btn"
        },

        _create: function () {
            this._super();
            var self = this;

            self.updateStatistics(self.options.rewardInfo);

            /**
             * js code to draw chart
             * Reference: https://developers.google.com/chart/interactive/docs/gallery/columnchart
             */
            google.charts.load('current', {packages: ['corechart', 'bar']});
            google.charts.setOnLoadCallback(drawColColors);
             
            function drawColColors() {
                var button = document.getElementById(self.options.buttonDivId);
                var chartDiv = document.getElementById(self.options.chartDivId);
            
                var options = {
                    title: '',
                    legend: { position: 'top' },
                    colors: ['#F5974D', '#32A8B4']
                };

                function drawDefaultChart() {
                    var defaultData = new google.visualization.arrayToDataTable(
                        self.options.rewardInfo.chart
                    );

                    var defaultChart = new google.visualization.ColumnChart(chartDiv);
                    defaultChart.draw(defaultData, options);

                    /* button.innerText = 'Change'; */
                    button.onclick = refreshChart;
                }
          
                function refreshChart() {
                    let data = {
                        period: $('#reports_chart_dateperiod').val(),
                        website: $('#reports_chart_website').val(),
                        group: $('#reports_chart_custgroup').val(),
                    };
    
                    $.ajax({
                        url: self.options.refreshUrl,
                        type: "POST",
                        data: data,
                        showLoader: true,
                        dataType: 'json',
                        success:function (response) {
                            if (response.success) {
                                $('body').trigger('processStop');

                                self.updateStatistics(response.data);

                                var refreshData = new google.visualization.arrayToDataTable(
                                    response.data.chart
                                );
                                var refreshChart = new google.visualization.ColumnChart(chartDiv);
                                refreshChart.draw(refreshData, options);
                            } else {
                                $('body').trigger('processStop');
                                alert({
                                    content: 'Something went wrong!'
                                });
                            }
                        }
                    });
                }

                drawDefaultChart();
            };
        },
        updateStatistics: function(data) {
            var selectedPeriodText = $("#reports_chart_dateperiod option:selected").text();
            $("#report_period_title").text(selectedPeriodText);

            $("#total_rewarded").text(data.total_rewarded);
            $("#total_redeemed").text(data.total_redeemed);
            $("#average_rewarded").text(data.average_rewarded);
            $("#average_redeemed").text(data.average_redeemed);
            $("#total_expired").text(data.total_expired);
        }
    });

    return $.mage.rewardpoints;
});
