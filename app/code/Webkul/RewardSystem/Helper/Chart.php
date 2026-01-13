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

namespace Webkul\RewardSystem\Helper;

use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord;
use Webkul\RewardSystem\Model\Reports\Period;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Chart extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory
     */
    protected $rewardRecordCollectionFactory;

    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory
     */
    protected $rewardDetailCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Period
     */
    protected $period;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Rewardrecord\CollectionFactory $rewardRecordCollectionFactory
     * @param Rewarddetail\CollectionFactory $rewardDetailCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Period $period
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Rewardrecord\CollectionFactory $rewardRecordCollectionFactory,
        Rewarddetail\CollectionFactory $rewardDetailCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Period $period,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->rewardRecordCollectionFactory = $rewardRecordCollectionFactory;
        $this->rewardDetailCollectionFactory = $rewardDetailCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->period = $period;
        $this->logger = $logger;
    }

    /**
     * Get Reward Info
     *
     * @param string $period
     * @param string $website
     * @param string $customerGroup
     * @return array
     */
    public function getRewardInfo($period = 'today', $website = 'all', $customerGroup = 'all')
    {
        $periodUnits = $this->period->getPeriodChartUnits();
        $unit = $periodUnits[$period];
        $periodNumbers = $this->period->getPeriodChartUnitsMaxNumber();
        $totalNum = $periodNumbers[$period];

        $totalRewardedPoints = 0.00;
        $totalRedeemedPoints = 0.00;

        $chartInfoArray[] = [
            __('Period'),
            __('Rewarded Points'),
            __('Redeemed Points')
        ];

        for ($i = $totalNum - 1; $i >= 0; $i--) {
            $time = strtotime("-$i $unit");
            if ($unit === 'day') {
                $axisPeriod = date('dM', $time);
                $from = date('Y-m-d 00:00:00', $time);
                $to = date('Y-m-d 23:59:59', $time);
            } elseif ($unit === 'month') {
                $axisPeriod = date('M', $time);
                $from = date('Y-m-01 00:00:00', $time);
                $to = date('Y-m-t 23:59:59', $time);
            } elseif ($unit === 'year') {
                $axisPeriod = date('Y', $time);
                $from = date('Y-01-01 00:00:00', $time);
                $to = date('Y-12-t 23:59:59', $time);
            }

            list(
                $rewardedPoints,
                $redeemedPoints
            ) = $this->getRewardPeriodData($from, $to);

            $chartInfoArray[] = [
                $axisPeriod,
                $rewardedPoints,
                $redeemedPoints
            ];

            $totalRewardedPoints = $totalRewardedPoints + $rewardedPoints;
            $totalRedeemedPoints = $totalRedeemedPoints + $redeemedPoints;
        }

        if ($period === 'all') {
            list(
                $totalRewardedPoints,
                $totalRedeemedPoints
            ) = $this->getRewardPeriodData();

            $totalExpiredPoints = $this->getExpiredRewardPoints();
        } else {
            $expireTo = date('Y-m-d 23:59:59');
            $toDecrease = $totalNum - 1;
            $time = strtotime("-$toDecrease $unit");
            if ($unit === 'day') {
                $expireFrom = date('Y-m-d 00:00:00', $time);
            } elseif ($unit === 'month') {
                $expireFrom = date('Y-m-01 00:00:00', $time);
            } elseif ($unit === 'year') {
                $expireFrom = date('Y-01-01 00:00:00', $time);
            }
            $totalExpiredPoints = $this->getExpiredRewardPoints($from, $to);
        }

        $averageRewardedPoints = $this->getAveragePerCustomer($totalRewardedPoints);
        $averageRedeemedPoints = $this->getAveragePerOrder($totalRedeemedPoints);

        return [
            'total_rewarded' => $totalRewardedPoints,
            'total_redeemed' => $totalRedeemedPoints,
            'total_expired' => $totalExpiredPoints,
            'average_rewarded' => $averageRewardedPoints,
            'average_redeemed' => $averageRedeemedPoints,
            'chart' => $chartInfoArray
        ];
    }

    /**
     * Get Reward Period Data
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getRewardPeriodData($from = '', $to = '')
    {
        $rewardedPoints = 0.00;
        $redeemedPoints = 0.00;

        $transactions = $this->rewardDetailCollectionFactory->create();
        if ($from) {
            $transactions->addFieldToFilter('transaction_at', ['gteq' => $from]);
        }
        if ($to) {
            $transactions->addFieldToFilter('transaction_at', ['lteq' => $to]);
        }

        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $rewardPoints = $transaction->getRewardPoint();
                if ($rewardPoints) {
                    if ($transaction->getAction() == 'credit') {
                        $rewardedPoints = $rewardedPoints + $rewardPoints;
                    } elseif ($transaction->getAction() == 'debit') {
                        $redeemedPoints = $redeemedPoints + $rewardPoints;
                    }
                }
            }
        }

        return [$rewardedPoints, $redeemedPoints];
    }

    /**
     * Get customer Expired Reward Points
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public function getExpiredRewardPoints($from = '', $to = '')
    {
        $expiredPoints = 0.00;

        // Reward Points expired by cron
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('is_expired', 1)
                        ->addFieldToFilter('action', 'credit');
        if ($from) {
            $transactions->addFieldToFilter('transaction_at', ['gteq' => $from]);
        }
        if ($to) {
            $transactions->addFieldToFilter('transaction_at', ['lteq' => $to]);
        }
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
        if ($from) {
            $transactions->addFieldToFilter('transaction_at', ['gteq' => $from]);
        }
        if ($to) {
            $transactions->addFieldToFilter('transaction_at', ['lteq' => $to]);
        }
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
}
