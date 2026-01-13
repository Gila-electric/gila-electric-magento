<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Auction\Helper\Data
     */
    protected $_rewardSystemHelper;

    /**
     * @var \Webkul\RewardSystem\Model\RewarddetailFactory
     */
    protected $_rewardDetailFactory;

    /**
     * @param \Webkul\Auction\Helper\Data                     $rewardSystemHelper
     * @param \Webkul\RewardSystem\Model\RewarddetailFactory  $rewardDetailFactory
     */
    public function __construct(
        \Webkul\RewardSystem\Helper\Data $rewardSystemHelper,
        \Webkul\RewardSystem\Model\RewarddetailFactory $rewardDetailFactory
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_rewardDetailFactory = $rewardDetailFactory;
    }
    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_rewardSystemHelper;
        if ($helper->getRewardApprovedOn() == 'shipment') {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $incrementId = $order->getIncrementId();
            $rewardPoint = 0;
            $rewardId = 0;
            $rewardModel = $this->_rewardDetailFactory->create()->getCollection()
                       ->addFieldToFilter('order_id', $order->getId())
                       ->addFieldToFilter('customer_id', $order->getCustomerId())
                       ->addFieldToFilter('status', 0);
            foreach ($rewardModel as $rewardData) {
                $rewardPoint = $rewardData->getRewardPoint();
                $rewardId = $rewardData->getId();
            }
            if ($rewardPoint) {
                $transactionNote = __('Order id : %1 credited amount', $incrementId);
                $customer_id = $order->getCustomerId();
                $rewardData = [
                'customer_id' => $customer_id,
                'points' => $rewardPoint,
                'type' => 'credit',
                'review_id' => 0,
                'order_id' => $order->getId(),
                'status' => 1,
                'note' => $transactionNote,
                'reward_id' => $rewardId
                ];
                $msg = __(
                    'You got %1 reward points on order #%2',
                    $rewardPoint,
                    $incrementId
                )->render();
                $adminMsg = __(
                    ' have placed an order on your site, and got %1 reward points',
                    $rewardPoint
                )->render();
                $helper->updateRewardRecordData($msg, $adminMsg, $rewardData);
                $data = ['status' => 1];
                $rewardDetailModel = $this->_rewardDetailFactory
                   ->create()
                   ->load($rewardId)
                   ->addData($data);
                $rewardDetailModel->setId($rewardId)->save();
                if ($helper->getrewardPriority() == 0) {
                    $helper->updateRewardOrderDetailData($order);
                }
            }
        }
    }
}
