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
namespace Webkul\RewardSystem\Block\Adminhtml\Reports\Statistics;

use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord;

class Info extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_RewardSystem::reports/statistics/info.phtml';

    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory
     */
    protected $rewardRecordCollectionFactory;

    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory
     */
    protected $rewardDetailCollectionFactory;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Rewardrecord\CollectionFactory $rewardRecordCollectionFactory
     * @param Rewarddetail\CollectionFactory $rewardDetailCollectionFactory
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Rewardrecord\CollectionFactory $rewardRecordCollectionFactory,
        Rewarddetail\CollectionFactory $rewardDetailCollectionFactory,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rewardRecordCollectionFactory = $rewardRecordCollectionFactory;
        $this->rewardDetailCollectionFactory = $rewardDetailCollectionFactory;
        $this->rewardHelper = $rewardHelper;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get customer Total Reward Points
     */
    public function getTotalRewardPoints()
    {
        $totalPoints = 0.00;
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('action', 'credit');
        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $rewardPoints = $transaction->getRewardPoint();
                if ($rewardPoints) {
                    $totalPoints = $totalPoints + $rewardPoints;
                }
            }
        }
        return $totalPoints;
    }

    /**
     * Get customer Used Reward Points
     */
    public function getUsedRewardPoints()
    {
        $usedPoints = 0.00;
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('action', 'debit');
        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $rewardPoints = $transaction->getRewardPoint();
                if ($rewardPoints) {
                    $usedPoints = $usedPoints + $rewardPoints;
                }
            }
        }
        /** Can also get information from 'reward_used' column with filter 'action' as 'credit' */
        return $usedPoints;
    }

    /**
     * Get customer Expired Reward Points
     */
    public function getExpiredRewardPoints()
    {
        $expiredPoints = 0.00;

        // Reward Points expired by cron
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('is_expired', 1)
                        ->addFieldToFilter('action', 'credit');
        $transactions->getSelect()->where('reward_point > reward_used OR reward_used IS NULL');
        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $pointsDiff = $transaction->getRewardPoint() - $transaction->getRewardUsed();
                if ($pointsDiff) {
                    $expiredPoints = $expiredPoints + $pointsDiff;
                }
            }
        }

        // Reward Points expired by admin
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('action', 'expire');
        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $rewardPoints = $transaction->getRewardPoint();
                if ($rewardPoints) {
                    $expiredPoints = $expiredPoints + $rewardPoints;
                }
            }
        }

        return $expiredPoints;
    }

    /**
     * Function getAveragePerCustomer
     *
     * @param float $rewardPoints
     * @return float
     */
    public function getAveragePerCustomer($rewardPoints)
    {
        $average = 0.00;
        $customerCollection = $this->customerFactory->create()->getCollection();
        $customerCount = count($customerCollection);
        if ($customerCount) {
            $average = $rewardPoints / $customerCount;
            $average = round($average, 2);
        }
        return $average;
    }

    /**
     * Function getAveragePerOrder
     *
     * @param float $rewardPoints
     * @return float
     */
    public function getAveragePerOrder($rewardPoints)
    {
        $average = 0.00;
        $ordersCollection = $this->orderFactory->create()->getCollection();
        $ordersCount = count($ordersCollection);
        if ($ordersCount) {
            $average = $rewardPoints / $ordersCount;
            $average = round($average, 2);
        }
        return $average;
    }

    /**
     * Get Helper Class
     */
    public function getRewardHelper()
    {
        return $this->rewardHelper;
    }
}
