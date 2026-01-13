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

use Magento\Framework\Session\SessionManager;
use Magento\Framework\Event\ObserverInterface;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Webkul\RewardSystem\Model\RewarddetailFactory;
use Magento\Quote\Model\QuoteFactory;

class MultiShipObserver implements ObserverInterface
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardSystemHelper;

    /**
     * @var \Webkul\RewardSystem\Model\RewarddetailFactory
     */
    protected $_rewardDetail;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $session;

    /**
     * @var Magento\Quote\Model\QuoteFactory;
     */
    protected $quoteFactory;

    /**
     * @param RewardSystemHelper $rewardSystemHelper
     * @param RewarddetailFactory $rewardDetail
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param SessionManager $session
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        RewarddetailFactory $rewardDetail,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        SessionManager $session,
        QuoteFactory $quoteFactory
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_rewardDetail = $rewardDetail;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->session = $session;
        $this->quoteFactory = $quoteFactory;
    }
    /**
     * Customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_rewardSystemHelper;
        $enableRewardSystem = $helper->enableRewardSystem();
        $status = 0;

        $order = $observer->getOrder();
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());
        $multiShipRewardData = $this->session->getMultiShipRewardData();
        if ($order->getShippingAddress()) {
            $customerAddressId = $order->getShippingAddress()->getCustomerAddressId();
            $quoteAddressModel = $this->quoteAddressFactory->create()->getCollection()
                                        ->addFieldToFilter('address_type', 'shipping')
                                        ->addFieldToFilter('quote_id', $order->getQuoteId())
                                        ->addFieldToFilter('customer_address_id', $customerAddressId)
                                        ->getFirstItem();
    
        }
        if (isset($quoteAddressModel) && $quoteAddressModel->getAddressId() &&
                  isset($multiShipRewardData[$quoteAddressModel->getAddressId()])) {
            $rewardAmount = $multiShipRewardData[$quoteAddressModel->getAddressId()];
            $order->setRewardAmount($rewardAmount);
            $rewardInfo1 = $helper->getRewardInfoFromQuote($quote);
            $multirewardInfo= [
                'used_reward_points' => $rewardAmount/ $this->_rewardSystemHelper->getRewardValue(),
                'number_of_rewards' => $rewardInfo1['number_of_rewards'],
                'avail_amount' => $rewardInfo1['avail_amount'],
                'amount' => $rewardAmount
            ];
            $helper->setRewardInfoInQuote($quote, $multirewardInfo);
        }
        $rewardInfo = $helper->getRewardInfoFromQuote($quote);
        if (!$order->canInvoice() && $helper->getRewardApprovedOn() == 'invoice') {
            $status = 1;
        }
            $customerId = $order->getCustomerId();
        if ($enableRewardSystem && $customerId) {
            $currencyCode = $order->getOrderCurrencyCode();
            $orderId = $order->getId();
            $incrementId = $order->getIncrementId();
            $this->addCreditAmountData($orderId, $customerId, $incrementId, $status, $order);
            if (is_array($rewardInfo)) {
                $this->deductRewardPointFromCustomer(
                    $customerId,
                    $incrementId,
                    $orderId,
                    $rewardInfo,
                    $order
                );
            }
        }
    }

    /**
     * Add Credit Amount Data
     *
     * @param int    $orderId
     * @param int    $customerId
     * @param int    $incrementId
     * @param int    $status
     * @param string $order
     */
    public function addCreditAmountData($orderId, $customerId, $incrementId, $status, $order = null)
    {
        $helper = $this->_rewardSystemHelper;
        $rewardPoint = $helper->calculateCreditAmountforOrder($orderId, $order);

        if ($rewardPoint > 0) {
            $transactionNote = __('Order id : %1 credited amount', $incrementId);
            $rewardData = [
              'customer_id' => $customerId,
              'points' => $rewardPoint,
              'type' => 'credit',
              'review_id' => 0,
              'order_id' => $orderId,
              'status' => $status,
              'note' => $transactionNote
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
            $helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
        }
    }

    /**
     * Deduct Reward Point From Customer
     *
     * @param int    $customerId
     * @param int    $incrementId
     * @param int    $orderId
     * @param array  $rewardInfo
     * @param string $order
     */
    public function deductRewardPointFromCustomer(
        $customerId,
        $incrementId,
        $orderId,
        $rewardInfo,
        $order = null
    ) {
        $helper = $this->_rewardSystemHelper;
        $transactionNote = __('Order id : %1 debited amount', $incrementId);
        $rewardPoints = $rewardInfo['used_reward_points'];
        $rewardPoint = $helper->calculateCreditAmountforOrder($orderId, $order);
        if ($rewardPoint> 0 && $rewardPoints > 0) {
            $rewardData = [
                'customer_id' => $customerId,
                'points' => $rewardPoints,
                'type' => 'debit',
                'review_id' => 0,
                'order_id' => $orderId,
                'status' => 1,
                'note' => $transactionNote
                ];
            $msg = __(
                'You used %1 reward points on order #%2',
                $rewardPoints,
                $incrementId
            )->render();
            $adminMsg = __(
                ' have placed an order on your site, and used %1 reward points',
                $rewardPoints
            )->render();
            $helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
        }
    }
}
