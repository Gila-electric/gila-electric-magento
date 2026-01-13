/**
 * Webkul_DailyDeals Category View Js
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "jquery/ui",
    "domReady!"
], function ($, $t) {
    "use strict";
    $.widget('deal.categoryview', {
        _create: function () {
            self = this;
            
                var viewCategoryOpt = self.options;
                var days    = 24*60*60,
                hours   = 60*60,
                minutes = 60;
                $.fn.countdown = function (prop) {
                    var options = $.extend({
                        callback    : function () {
                                        alert("");
                                    },
                        timestamp   : 0
                    },prop);
                    var left, d, h, m, s, positions;
                    positions = this.find('.position');
                    var initialize =  setInterval(function () {
                        left = Math.floor((options.timestamp - (new Date())) / 1000);
                        if (left < 0) {
                            left = 0;
                        }
                        d = Math.floor(left / days);
                        left -= d*days;
                        h = Math.floor(left / hours);
                        left -= h*hours;
                        m = Math.floor(left / minutes);
                        left -= m*minutes;
                        s = left;
                        options.callback(d, h, m, s);
                        if (d==0 && h==0 && m==0 && s==0) {
                            clearInterval(initialize);
                        }
                    }, 1000);
                    return this;
                };
                
                $('.deal.wk-daily-deal').each(function () {
                    if($("table.data.grouped").length!=0)
                {
                    $('.deal.wk-daily-deal').each(function (ind1, val1) {
                        $(val1).appendTo($('table.grouped .price-box.price-final_price[data-product-id="'+$(val1).data('deal-id')+'"]').closest('td'));
                    })
                 
                }
                    var dealBlock = $(this),
                        colckElm  = dealBlock.find('.wk_cat_count_clock'),
                        timeStamp = new Date(2012, 0, 1),
                        stopTime  = colckElm.attr('data-stoptime'),
                        newYear   = true;
                    if ((new Date()) > timeStamp) {
                        timeStamp = colckElm.attr('data-diff-timestamp')*1000;
                        timeStamp = (new Date()).getTime() + timeStamp;
                        newYear = false;
                    }
                    if (colckElm.length) {
                        colckElm.countdown({
                            timestamp : timeStamp,
                            callback : function (days, hours, minutes, seconds) {
                                var message = "",
                                    timez = "",
                                    distr = stopTime.split(' '),
                                    tzones =  distr[0].split('-'),
                                    months = [
                                        'January',
                                        'February',
                                        'March',
                                        'April',
                                        'May',
                                        'June',
                                        'July',
                                        'August',
                                        'September',
                                        'October',
                                        'November',
                                        'December'
                                    ];
                                if (days < 10) {
                                        days = "0"+days;
                                }
                                if (hours < 10) {
                                        hours = "0"+hours;
                                }
                                if (minutes < 10) {
                                    minutes = "0"+minutes;
                                }
                                if (seconds < 10) {
                                    seconds = "0"+seconds;
                                }
                                message += '<span class="wk_front_dd_set_time_days wk-deal-clock-span" id="wk-deal-dd" title="Days">'+days+' ,<span class="label wk-deal-clock-label-dd" for="wk-deal-dd"><span>'+$t("Days")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-hr" title="Hours"> '+hours+' :<span class="label wk-deal-clock-label-hr" for="wk-deal-hr"><span>'+$t("Hours")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-mi" title="Minutes"> '+minutes+' :<span class="label wk-deal-clock-label-mi" for="wk-deal-mi"><span>'+$t("Minutes")+'</span></span></span>';
                                message += '<span class="wk_front_dd_set_time wk-deal-clock-span" id="wk-deal-sec" title="Seconds"> '+seconds+' <span class="label wk-deal-clock-label-sec" for="wk-deal-sec"><span>'+$t("Seconds")+'</span></span></span>';
                                colckElm.html(message);
                                if (hours == 0 && minutes == 0 && seconds == 0) {
                                    $.ajax({
                                        url: dealBlock.attr('data-update-url'),
                                        data: {'deal-id':dealBlock.attr('data-deal-id')},
                                        type: 'POST',
                                        dataType:'html',
                                        success: function (transport) {
                                            var response = $.parseJSON(transport);
                                        }
                                    });
                                    var priceBox = dealBlock.prev('.price-box');
                                    if (priceBox.length ==0) {
                                        priceBox = dealBlock.prev('.product-info-price').find('.price-box');
                                        priceBox.find('.special-price').remove();
                                        priceBox.find('.price-label').remove();
                                       dealBlock.remove();
                                       priceBox.find('.old-price').addClass('price').removeClass('old-price');
                                    }
                                }
                            }
                                });
                            }
                        });
        }
    });
    return $.deal.categoryview;
});
