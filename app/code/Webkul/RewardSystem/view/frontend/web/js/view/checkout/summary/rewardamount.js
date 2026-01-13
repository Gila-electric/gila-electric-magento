/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Webkul_RewardSystem/checkout/cart/totals/rewardamount' /** Call Html Template */
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                var price = 0;
                if (this.totals()) {
                    /**
                     * Name of xml Item in getSegment
                     */
                    price = totals.getSegment('reward_amount').value;
                }
                if (price) {
                    return true;
                } else {
                    return false;
                }
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('reward_amount').value;
                }
                return this.getFormattedPrice(this.getBaseValue());
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('reward_amount').value) {
                    price = parseFloat(totals.getSegment('reward_amount').value);
                }
                return price;
            }
        });
    }
);
