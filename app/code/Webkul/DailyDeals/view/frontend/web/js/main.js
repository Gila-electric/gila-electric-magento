/**
 * Webkul_DailyDeals Category View Js
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require([
    "jquery",
    'mage/translate',
], function ($, $t) {
    $(function () {
        // console.log(window.dailyurl);
        window.wkdailydealLoaded = false;
        var dataloaded = false;
        var updatedPro = [];
        var url=window.dailyurl
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            cache: false,
            success: function (response) {
                if (response.success) {
                    dataloaded = true;
                    var data = response.data;
                    $.each($('.deal.wk-daily-deal'), function (ind, val) {
                        var dealId = $(val).data('deal-id');
                        if (dealId != undefined && dealId) {
                            if (data[dealId]!=undefined) {
                                updatedPro.push(dealId);
                                updatedPro = updatedPro.concat(data[dealId]['parent']);
                                var countClock = $(val).find('.wk_cat_count_clock');
                                $(countClock).attr('data-stoptime', data[dealId]['stoptime']);
                                $(countClock).attr('data-diff-timestamp', data[dealId]['diff_timestamp']);
                            } else {
                                dataloaded = false;
                            }
                        }
                    });
                    $.each($('div.price-box'), function (ind1, val1) {
                        var productId = $(val1).data('product-id');
                        if (productId != undefined && productId) {
                            if (data[productId]!=undefined && !(updatedPro.includes(productId) || updatedPro.includes(productId+''))) {
                                if (!$(val1).closest('.products-upsell').length) {
                                    dataloaded = false;
                                }
                            }
                        }
                    });
                    if (dataloaded) {
                        $(document).ready(function () {
                            $('body').trigger('wkdailydealLoaded');
                            window.wkdailydealLoaded = true;
                        });
                    } else {
                        $(document).ready(function () {
                        $('body').trigger('wkdailydealLoaded');
                        window.wkdailydealLoaded = true;
                        });

                        $.ajax({
                            type: "get",
                            url: window.BASE_URL+"dailydeals/index/cacheflush",
                            dataType: "json",
                            cache: false,
                            success: function (response) {
                            }
                        });
                    }
                } 
            },
            error: function (response) {
            }
        });
    });
});
