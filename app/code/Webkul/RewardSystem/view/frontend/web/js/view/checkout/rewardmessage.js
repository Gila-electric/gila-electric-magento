/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'Webkul_RewardSystem/js/model/reward'
], function ($, Component, rewardData) {
    'use strict';
    
    var RewardMessage = rewardData.getRewardMessage();
    
    return Component.extend({
        defaults: {
            template: 'Webkul_RewardSystem/checkout/rewardloginmessage'
        },

        checkStatus: function() {
            if(RewardMessage.status == 0) {
                return false;
            } else {
                return true;
            }
        },
        
        getValue: function () {
            return RewardMessage.total_reward_point;
        },

        getRedirectUrl: function() {
            return RewardMessage.url;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            return this;
        }
    });
});
