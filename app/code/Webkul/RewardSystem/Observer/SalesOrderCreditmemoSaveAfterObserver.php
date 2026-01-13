<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $rewardSystemHelper;

    /**
     * @var \Webkul\RewardSystem\Model\RewarddetailFactory
     */
    protected $rewardDetailFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Webkul\RewardSystem\Helper\Data                $rewardSystemHelper
     * @param \Webkul\RewardSystem\Model\RewarddetailFactory  $rewardDetailFactory
     * @param \Psr\Log\LoggerInterface                        $logger
     */

    public function __construct(
        \Webkul\RewardSystem\Helper\Data $rewardSystemHelper,
        \Webkul\RewardSystem\Model\RewarddetailFactory $rewardDetailFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->rewardSystemHelper = $rewardSystemHelper;
        $this->rewardDetailFactory = $rewardDetailFactory;
        $this->logger = $logger;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->rewardSystemHelper;
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $incrementId = $order->getIncrementId();
        $rewardPoint = 0;
        $isRewardUsed = false;
        $rewardDetailsModel = $this->rewardDetailFactory->create()->getCollection()
                     ->addFieldToFilter('order_id', $order->getId())
                     ->addFieldToFilter('action', ['eq' =>'debit'])
                     ->addFieldToFilter('customer_id', $order->getCustomerId())
                     ->addFieldToFilter('status', 1)
                     ->addFieldToFilter('is_revert', 0);
        if ($rewardDetailsModel->getSize()) {
            $isRewardUsed = true;
        }
        $rewardModel = $this->rewardDetailFactory->create()->getCollection()
                     ->addFieldToFilter('order_id', $order->getId())
                     ->addFieldToFilter('action', ['eq' =>'credit'])
                     ->addFieldToFilter('customer_id', $order->getCustomerId())
                     ->addFieldToFilter('status', 1)
                     ->addFieldToFilter('is_revert', 0);
        foreach ($rewardModel as $rewardsData) {
            $rewardPoint = $rewardsData->getRewardPoint();
            // getting reward points according to the items of an order
            $points = 0;
            foreach ($order->getAllVisibleItems() as $_item) {
                $orderItem = $_item->getData();
                if ($orderItem["qty_refunded"] > 0) {
                    $refundItemId = $_item->getProductId();
                    $points += $helper->getRewardOrderDetailData($order, $refundItemId, $orderItem["qty_refunded"]);
                }
            }
            if ($points > 0) {
                $rewardPoint = $points;
            }
            if ($rewardPoint) {
                $transactionNote = __('Order id : %1 Debited amount on order item cancel', $incrementId);
                $rewardData = [
                  'customer_id' => $order->getCustomerId(),
                  'points' => $rewardPoint,
                  'type' => 'debit',
                  'review_id' => 0,
                  'is_revert' => 1,
                  'order_id' => $order->getId(),
                  'status' => 1,
                  'note' => $transactionNote
                ];
                $msg = __(
                    'Revert %1 reward points on order #%2 item cancel',
                    $rewardPoint,
                    $incrementId
                )->render();
                $adminMsg = __(
                    ' Revert %1 reward points on order #%2 item cancel',
                    $rewardPoint,
                    $incrementId
                )->render();
                $rewardsData->setIsRevert(1)->save();
                $helper->setDataFromAdmin($msg, $adminMsg, $rewardData);
            }
        }
        if ($isRewardUsed) {
            $rewardPoint = 0;
            foreach ($rewardDetailsModel as $rewardsData) {
                 $rewardPoint = $rewardsData->getRewardPoint();
                //Checking partial refund
                $refundPercentage = $order->getTotalRefunded() / ($order->getSubTotal() + $order->getRewardAmount());
                $rewardPoint = $rewardPoint * (($refundPercentage < 1) ? $refundPercentage : 1);
                if ($rewardPoint) {
                    $transactionNote = __('Order id : %1 Credited amount on order item cancel', $incrementId);
                    $rewardData = [
                      'customer_id' => $order->getCustomerId(),
                      'points' => $rewardPoint,
                      'type' => 'credit',
                      'review_id' => 0,
                      'is_revert' => 1,
                      'order_id' => $order->getId(),
                      'status' => 1,
                      'note' => $transactionNote
                    ];
                    $msg = __(
                        'Revert %1 reward points on order #%2 item cancel',
                        $rewardPoint,
                        $incrementId
                    )->render();
                    $adminMsg = __(
                        ' Revert %1 reward points on order #%2 item cancel',
                        $rewardPoint,
                        $incrementId
                    )->render();
                    $rewardsData->setIsRevert(1)->save();
                    $helper->setDataFromAdmin($msg, $adminMsg, $rewardData);
                }
            }
        }
    }
}
