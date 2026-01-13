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

namespace Webkul\RewardSystem\Block;

use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord;
use Magento\Sales\Model\Order;
use Magento\Framework\Json\Helper\Data;

class RewardPoints extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\RewardSystem\Model\ResourceModel\RewardDetail\CollectionFactory
     */
    protected $rewardDetailCollectionFactory;
    /**
     * @var rewardDetailCollection
     */
    protected $rewardDetailCollection;
    /**
     * @var Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory
     */
    protected $rewardRecordCollectionFactory;
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonData;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Rewardrecord\CollectionFactory $rewardRecordCollectionFactory
     * @param Rewarddetail\CollectionFactory $rewardDetailCollectionFactory
     * @param Order $order
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param Data $jsonData
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Rewardrecord\CollectionFactory $rewardRecordCollectionFactory,
        Rewarddetail\CollectionFactory $rewardDetailCollectionFactory,
        Order $order,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        Data $jsonData,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rewardRecordCollectionFactory = $rewardRecordCollectionFactory;
        $this->rewardDetailCollectionFactory = $rewardDetailCollectionFactory;
        $this->order = $order;
        $this->rewardHelper = $rewardHelper;
        $this->pricingHelper = $pricingHelper;
        $this->jsonData = $jsonData;
        $this->filterProvider = $filterProvider;
    }

    /**
     * Prepare Layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRewardDetailCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'rewardsystem.rewarddetail.pager'
            )
            ->setCollection(
                $this->getRewardDetailCollection()
            );
            $this->setChild('pager', $pager);
            $this->getRewardDetailCollection()->load();
        }

        return $this;
    }
    /**
     * Get Pager Html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare HTML content.
     *
     * @param mixed $value
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        return $this->filterProvider->getPageFilter()->filter($value);
    }

    /**
     * Get customer's Remaining Reward Points
     *
     * @param int $customerId
     */
    public function getRemainingRewardPoints($customerId)
    {
        $remainingPoints = 0;
        $rewardRecord = $this->rewardHelper->getRewardRecordOfCustomer($customerId);
        if ($rewardRecord) {
            $remainingPoints = $rewardRecord->getRemainingRewardPoint();
        }
        return $remainingPoints;
    }

    /**
     * Get customer's Total Reward Points
     *
     * @param int $customerId
     */
    public function getTotalRewardPoints($customerId)
    {
        $totalPoints = 0;
        $rewardRecord = $this->rewardHelper->getRewardRecordOfCustomer($customerId);
        if ($rewardRecord) {
            $totalPoints = $rewardRecord->getTotalRewardPoint();
        }
        return $totalPoints;
    }

    /**
     * Get customer's Used Reward Points
     *
     * @param int $customerId
     */
    public function getUsedRewardPoints($customerId)
    {
        $usedPoints = 0;
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $customerId)
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
     * Get customer's Expired Reward Points
     *
     * @param int $customerId
     */
    public function getExpiredRewardPoints($customerId)
    {
        $expiredPoints = 0;

        // Reward Points expired by cron
        $transactions = $this->rewardDetailCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $customerId)
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
                        ->addFieldToFilter('customer_id', $customerId)
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
     * Get customer's Expiry Reward Points
     *
     * @param int $customerId
     */
    public function getExpiryRewardPoints($customerId)
    {
        $expiryPoints = 0;
        $expiresAt = "-";
        $expiresDays = (int)$this->rewardHelper->getConfigData('expires_after_days');
        if ($expiresDays) {
            $transactions = $this->rewardDetailCollectionFactory->create()
                          ->addFieldToFilter('customer_id', $customerId)
                          ->addFieldToFilter('is_expired', 0)
                          ->addFieldToFilter('action', 'credit')
                          ->setOrder('expires_at', 'ASC');
            $transactions->getSelect()->where('reward_point > reward_used OR reward_used IS NULL');
            if ($transactions->getSize()) {
                foreach ($transactions as $transaction) {
                    $expiryPoints = $transaction->getRewardPoint() - $transaction->getRewardUsed();
                    if ($expiryPoints && $transaction->getExpiresAt()) {
                        $expiresAt = $this->rewardHelper->formatDate(
                            $transaction->getExpiresAt(),
                            $format = \IntlDateFormatter::MEDIUM
                        );
                        break;
                    }
                }
            }
        }
        return [$expiryPoints, $expiresAt];
    }

    /**
     * Get reward detail collection of a customer
     */
    public function getRewardDetailCollection()
    {
        $param = $this->getRequest()->getParams();
        if (!$this->rewardDetailCollection) {
            $customerId = $this->rewardHelper
                ->getCustomerId();
            $rewardDetailCollection = $this->rewardDetailCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);
            if (!isset($param['srt'])) {
                $rewardDetailCollection->setOrder('transaction_at', 'DESC');
            }
                
            if (isset($param['st']) && $param['st']) {
                $status;
                if ($param['st'] == 'Applied') {
                    $param['st'] = 'debit';
                    $status = 1;
                } elseif ($param['st'] == 'Approved') {
                    $param['st'] = 'credit';
                    $status = 1;
                } elseif ($param['st'] == 'Pending') {
                    $param['st'] = 'credit';
                    $status = 0;
                } elseif ($param['st'] == 'Expired') {
                    $param['st'] = 'expire';
                    $status = 1;
                } elseif ($param['st'] == 'Cancelled') {
                    $param['st'] = 'credit';
                    $status = 2;
                } else {
                    $param['st'] = $param['st'] ;
                    $status = 1;
                }
                $rewardDetailCollection->addFieldToFilter('status', $status)
                    ->addFieldToFilter('action', $param['st']);
            }
            if (isset($param['ty']) && $param['ty']) {
                $rewardDetailCollection->addFieldToFilter('action', $param['ty']);
            }
            if (isset($param['pt']) && $param['pt']) {
                $rewardDetailCollection->addFieldToFilter('reward_point', $param['pt']);
            }
           
            if (isset($param['srt']) && $param['srt']) {
                $rewardDetailCollection->setOrder('reward_point', $param['srt']);
            }
            $this->rewardDetailCollection = $rewardDetailCollection;
        }
        return $this->rewardDetailCollection;
    }

    /**
     * Get Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get Helper Class
     */
    public function getHelperClass()
    {
        return $this->rewardHelper;
    }

    /**
     * Get JSON helper
     */
    public function getJsonHelper()
    {
        return $this->jsonData;
    }
}
