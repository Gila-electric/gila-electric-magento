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

namespace Webkul\RewardSystem\Plugin\Block;

use Magento\Catalog\Block\Product\ListProduct;

class ProductListUpdate
{
    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->rewardHelper = $rewardHelper;
        $this->registry = $registry;
    }

    /**
     * After get product price
     *
     * @param ListProduct $list
     * @param float $result
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function afterGetProductPrice(
        ListProduct $list,
        $result,
        $product
    ) {
        $enableRewardSystem = $this->rewardHelper->enableRewardSystem();
        $rewardInfoHtml = '';
        if ($enableRewardSystem) {
            $rewardPriorityStatus = $this->rewardHelper->getrewardPriority();
            if ($rewardPriorityStatus == 2) {
                $currentCategory = $this->getCurrentCategory();
                $categoryId = $currentCategory->getId();
                list(
                    $categoryRewardPoints,
                    $status,
                    $message
                ) = $this->rewardHelper->getCategoryRewardToShow($categoryId);
                if ($categoryRewardPoints) {
                    $rewardInfoHtml =
                    '<div class="wk_rs_category_list"><span class="wk_rs_category_page_greet"></span>'.
                        __('Buy and Earn') . '<span class="wk_rs_price"> '. $categoryRewardPoints . ' RP' . '</span>'.
                    '</div>';
                }
            } elseif ($rewardPriorityStatus == 0) {
                list(
                    $productRewardPoints,
                    $minPrice,
                    $maxPrice,
                    $pointsRequired,
                    $sumOfRewardPoints,
                    $minRewardPoint,
                    $maxRewardPoint,
                    $status,
                    $message
                ) = $this->rewardHelper->getProductRewardInfo($product);
                if ($productRewardPoints) {
                    if ($product->getTypeId() == 'grouped' || ($product->getTypeId() == 'configurable'
                        && $sumOfRewardPoints > 0 && $pointsRequired > $minRewardPoint) ||
                        ($product->getTypeId() == 'bundle' && $pointsRequired > $minRewardPoint)) {
                        $spanText = $minRewardPoint . ' to ' . $productRewardPoints . ' RP';
                    } else {
                        $spanText = $productRewardPoints . ' RP';
                    }
                    $rewardInfoHtml =
                    '<div class="wk_rs_category_list"><span class="wk_rs_category_page_greet"></span>'.
                        __('Buy and Earn') . '<span class="wk_rs_price"> '. $spanText . '</span>'.
                    '</div>';
                }
            }
        }
        return $rewardInfoHtml . $result;
    }

    /**
     * Function to get current category on category list page
     *
     * @return void
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
}
