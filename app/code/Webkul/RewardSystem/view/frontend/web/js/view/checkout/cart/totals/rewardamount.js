/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define(
    [
        'Webkul_RewardSystem/js/view/checkout/summary/rewardamount',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, defaultTotal, totals) {
        'use strict';
        
        return Component.extend({

            /**
             * Reward application procedure
             */
            initialize: function () {
                this._super();
                defaultTotal.estimateTotals();
            },

            /**
             * @override
             */
            isDisplayed: function () {
                var price = 0;
                if (this.totals()) {
                    /**
                     * Name of xml Item in getSegment
                     */
                  if (totals.getSegment('reward_amount') !== null) {
                    price = totals.getSegment('reward_amount').value;
                  }
                }
                price = parseFloat(price);
                if (price) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    }
);
