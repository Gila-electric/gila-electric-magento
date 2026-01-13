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
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteFactory;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Webkul\RewardSystem\Model\RewardrecordFactory;
use Webkul\RewardSystem\Model\RewarddetailFactory;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var RewardrecordFactory
     */
    protected $_rewardrecordFactory;

    /**
     * @var RewardSystemHelper
     */
    protected $_rewardSystemHelper;

    /**
     * @var RewarddetailFactory
     */
    protected $_rewardDetail;

    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $_orderModel;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var Magento\Quote\Model\QuoteFactory;
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param OrderFactory $orderModel
     * @param RewardSystemHelper $rewardSystemHelper
     * @param RewardrecordFactory $rewardRecordModel
     * @param RewarddetailFactory $rewardDetail
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param SessionManager $session
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        OrderFactory $orderModel,
        RewardSystemHelper $rewardSystemHelper,
        RewardrecordFactory $rewardRecordModel,
        RewarddetailFactory $rewardDetail,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        SessionManager $session,
        QuoteFactory $quoteFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->_orderModel = $orderModel;
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_rewardrecordFactory = $rewardRecordModel;
        $this->_rewardDetail = $rewardDetail;
        $this->dateTime = $dateTime;
        $this->session = $session;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Sales Order Place After sales_model_service_quote_submit_success event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_rewardSystemHelper;
        $enableRewardSystem = $helper->enableRewardSystem();
        $status = 0;
        $order = $observer->getOrder();
        if (!$order->canInvoice() && $helper->getRewardApprovedOn() == 'invoice') {
            $status = 1;
        }
        $customerId = $order->getCustomerId();
        if ($enableRewardSystem && $customerId) {
            $currencyCode = $order->getOrderCurrencyCode();
            $orderId = $order->getId();
            if ($this->alreadyAddedInData($orderId)) {
                return;
            }
            $quote = $this->quoteFactory->create()->load($order->getQuoteId());
            $rewardInfo = $helper->getRewardInfoFromQuote($quote);
            $incrementId = $order->getIncrementId();
            if (is_array($rewardInfo)) {
                $this->deductRewardPointFromCustomer(
                    $customerId,
                    $incrementId,
                    $orderId,
                    $rewardInfo
                );
            }
            $this->addCreditAmountData($orderId, $customerId, $incrementId, $status);
            $helper->unsetRewardInfoInQuote($quote);
        }
    }

    /**
     * Add Credit Amount Data
     *
     * @param int $orderId
     * @param int $customerId
     * @param int $incrementId
     * @param int $status
     */
    public function addCreditAmountData($orderId, $customerId, $incrementId, $status)
    {
        $helper = $this->_rewardSystemHelper;
        $rewardPoint = $helper->calculateCreditAmountforOrder($orderId);
        if ($rewardPoint > 0) {
            $transactionNote = $helper->getTransactionNotePriorityWise($incrementId);
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
     * @param int   $customerId
     * @param int   $incrementId
     * @param int   $orderId
     * @param array $rewardInfo
     */
    public function deductRewardPointFromCustomer(
        $customerId,
        $incrementId,
        $orderId,
        $rewardInfo
    ) {
        $helper = $this->_rewardSystemHelper;
        $transactionNote = __('Order id : %1 debited amount', $incrementId);
        $rewardPoints = $rewardInfo['used_reward_points'];
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

    /**
     * Already Added In Data
     *
     * @param int $orderId
     */
    public function alreadyAddedInData($orderId)
    {
        $rewardDetailCollection = $this->_rewardDetail
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', ['eq'=>$orderId]);

        if ($rewardDetailCollection->getSize()) {
            return true;
        }
        return false;
    }
}
