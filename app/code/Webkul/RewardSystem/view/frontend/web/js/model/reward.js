/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var rewardData = window.checkoutConfig.rewards;
        var rewardSession = window.checkoutConfig.rewardSession;
        var rewardMessage = window.checkoutConfig.rewardMessage;
        return {
            rewardData: rewardData,
            rewardSession: rewardSession,
            rewardMessage: rewardMessage,

            getRewardData: function () {
                return rewardData;
            },

            getRewardSession: function () {
                return rewardSession;
            },

            getRewardMessage: function () {
                return rewardMessage;
            },
        };
    }
);
