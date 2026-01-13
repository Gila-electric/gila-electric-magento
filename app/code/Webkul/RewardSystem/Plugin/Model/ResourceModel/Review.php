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

namespace Webkul\RewardSystem\Plugin\Model\ResourceModel;

use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;

class Review
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardSystemHelper;

    /**
     * @var \Webkul\RewardSystem\Model\RewarddetailFactory
     */
    protected $_rewardDetailFactory;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_catalogFactory;

    /**
     * Constructor
     *
     * @param \Webkul\RewardSystem\Helper\Data               $rewardSystemHelper
     * @param \Magento\Framework\App\Request\Http            $request
     * @param \Magento\Review\Model\ReviewFactory            $reviewFactory
     * @param \Magento\Catalog\Model\ProductFactory          $catalogFactory
     * @param \Webkul\RewardSystem\Model\RewarddetailFactory $rewardDetailFactory
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $catalogFactory,
        \Webkul\RewardSystem\Model\RewarddetailFactory $rewardDetailFactory
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_request = $request;
        $this->_reviewFactory = $reviewFactory;
        $this->_catalogFactory = $catalogFactory;
        $this->_rewardDetailFactory = $rewardDetailFactory;
    }

    /**
     * After Aggregate
     *
     * @param \Magento\Review\Model\ResourceModel\Review $subject
     * @param array $result
     */
    public function afterAggregate(
        \Magento\Review\Model\ResourceModel\Review $subject,
        $result
    ) {
        $helper = $this->_rewardSystemHelper;
        $enableRewardSystem = $helper->enableRewardSystem();
        if ($helper->getAllowReview() && $enableRewardSystem) {
            $params = $this->_request->getParams();
            if (array_key_exists('id', $params)) {
                $reviewId = $params['id'];
                $this->updateReviewForReviewId($reviewId);
            } elseif (array_key_exists('reviews', $params)) {
                foreach ($params['reviews'] as $key => $reviewId) {
                    $this->updateReviewForReviewId($reviewId);
                }
            }
        }
        return $result;
    }

    /**
     * Check Review Id
     *
     * @param int $reviewId
     */
    public function checkReviewId($reviewId)
    {
        $reviewDetailCollection = $this->_rewardDetailFactory->create()
            ->getCollection()
            ->addFieldToFilter('review_id', ['eq'=>$reviewId]);
        if ($reviewDetailCollection->getSize()) {
            return false;
        }
        return true;
    }

    /**
     * Update Review For Review Id
     *
     * @param int $reviewId
     */
    public function updateReviewForReviewId($reviewId)
    {
        $helper = $this->_rewardSystemHelper;
        $reviewStatus = $this->checkReviewId($reviewId);
        if ($reviewStatus) {
            $reviewModel = $this->_reviewFactory->create()->load($reviewId);
            $productId = $reviewModel->getEntityPkValue();
            $product = $this->_catalogFactory->create()->load($productId);
            $customerId = $reviewModel->getCustomerId();
            if ($reviewModel->getStatusId() == \Magento\Review\Model\Review::STATUS_APPROVED && $customerId) {
                $rewardPoints = $helper->getRewardOnReview();
                $transactionNote = __("Reward Point for product review on %1", $product->getName());
                $rewardData = [
                    'customer_id' => $customerId,
                    'points' => $rewardPoints,
                    'type' => 'credit',
                    'review_id' => $reviewModel->getReviewId(),
                    'order_id' => 0,
                    'status' => 1,
                    'note' => $transactionNote
                ];
                $msg = __(
                    'You got %1 reward points for product review',
                    $rewardPoints
                )->render();
                $adminMsg = __(
                    "'s product review get approved, and got %1 reward points",
                    $rewardPoints
                )->render();
                $this->_rewardSystemHelper->setDataFromAdmin(
                    $msg,
                    $adminMsg,
                    $rewardData
                );
            }
        }
    }
}
