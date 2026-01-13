<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Plugin\Checkout\CustomerData;

class Cart
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data;
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Function after GetSectionData
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, array $result)
    {
        $result['reward_earn_points'] = $this->getRewardInfo();
        return $result;
    }

    /**
     * Function get Reward Info Html
     *
     * @return string
     */
    protected function getRewardInfo()
    {
        $rewardInfo = false;
        $helper = $this->helper;
        $enableRewardSystem = $helper->enableRewardSystem();
        $cartQty = $helper->getCartData();

        if ($enableRewardSystem && $cartQty) {
            $rewardPriorityStatus = $helper->getrewardPriority();
            if ($rewardPriorityStatus == 0) {
                $totalRewardPoints = 0;
                $quantityWise = $helper->getrewardQuantityWise();
                $cartAllData =  $helper->getCartAllData();
                foreach ($cartAllData as $singleItem) {
                    $productId = $singleItem->getProductId();
                    $pdtQty = $singleItem->getQty();
                    list($productRewardPoints, $status, $message) = $helper->getProductRewardToShow($productId);
                    if ($singleItem['parent_item_id'] != null) {
                        $singleItem->getParentItem();
                        $pdtQty = $singleItem->getParentItem()->getQty();
                    }
                    if ($quantityWise) {
                        $totalRewardPoints += ($pdtQty*$productRewardPoints);
                    } else {
                        $totalRewardPoints += $productRewardPoints;
                    }
                }
                if ($totalRewardPoints) {
                    $rewardInfoHtml =
                        '<span class="wk_reward_required">' . __('Checkout now to') . '</span> '.
                        '<span class="wk_rs_cart_green wk_rs_bold_text">'
                            . __('earn %1 Reward Points', $totalRewardPoints) . '</span>';
                    $rewardInfo = $totalRewardPoints;
                }
            } elseif ($rewardPriorityStatus == 1) {
                $cartRewardDetails = $helper->getCartReward();
                $cartRewardPoints = isset($cartRewardDetails['reward']) ? $cartRewardDetails['reward'] : 0;
                $amountFrom = $helper->getformattedPrice($cartRewardDetails['amount_from']);
                $amountTo = $helper->getformattedPrice($cartRewardDetails['amount_to']);
                if ($cartRewardPoints) {
                    $rewardInfoHtml =
                    '<span class="wk_reward_required">' . __('Checkout now to') . '</span> '.
                        '<span class="wk_rs_cart_green wk_rs_bold_text">'
                            . __('earn %1 Reward Points', $cartRewardPoints) . '</span>';
                    $rewardInfo = $cartRewardPoints;
                }
            }
        }
        return $rewardInfo;
    }
}
