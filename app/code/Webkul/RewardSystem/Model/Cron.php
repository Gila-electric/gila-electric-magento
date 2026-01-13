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

namespace Webkul\RewardSystem\Model;

class Cron
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\RewardSystem\Model\Rewarddetail
     */
    protected $rewarddetail;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                      $date
     * @param \Webkul\RewardSystem\Helper\Data                                 $helper
     * @param \Webkul\RewardSystem\Model\Rewarddetail                          $rewarddetail
     * @param \Magento\Framework\Model\Context                                 $context
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\RewardSystem\Helper\Data $helper,
        \Webkul\RewardSystem\Model\RewarddetailFactory $rewarddetail,
        \Magento\Framework\Model\Context $context
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->date = $date;
        $this->helper = $helper;
        $this->rewarddetail = $rewarddetail;
        $this->context = $context;
    }

    /**
     * Update Reward
     *
     * @return void
     */
    public function updateReward()
    {
        $helper = $this->helper;
        $date = $this->date->gmtDate(
            'Y-m-d',
            $this->date->gmtTimestamp()
        );
        $this->expirePoints($date);
        $this->sendPointsExpireEmail();
        if ($helper->getConfigData('reward_on_birthday')) {
            $this->earnBirthdayPoints();
        }
    }

    /**
     * Expire Points
     *
     * @param bool|string $now
     * @return void
     */
    public function expirePoints($now = false)
    {
        if (!$now) {
            $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }

        $transactions = $this->rewarddetail->create()->getCollection()
                ->addFieldToFilter('is_expired', 0)
                ->addFieldToFilter('action', 'credit');
        $transactions->getSelect()->where('expires_at < "'.$now.'"')
                                  ->where('reward_point > reward_used OR reward_used IS NULL');
        foreach ($transactions as $transaction) {
            $transaction->setIsExpired(1)
                        ->save();
            $rewardPoints = $transaction->getRewardPoint() - $transaction->getRewardUsed();
            $transactionNote = __('%1 Point(s) are expired', $rewardPoints);
            $rewardData = [
                'customer_id' => $transaction->getCustomerId(),
                'points' => $rewardPoints,
                'type' => 'expire',
                'review_id' => 0,
                'order_id' => 0,
                'status' => 1,
                'is_expired' => 1,
                'note' => $transactionNote
            ];
            $msg = __(
                'Your %1 reward points have been expired',
                $rewardPoints
            )->render();
            $adminMsg = __(
                ' reward points expired %1',
                $rewardPoints
            )->render();
            $this->helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
        }
    }

    /**
     * Earn Birthday Points
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function earnBirthdayPoints()
    {
        $helper = $this->helper;
        $days = $helper->getConfigData('birthday_after_days');
        $rewardPoints = $helper->getConfigData('birthday_reward');
        if ($days >= 0) {
            $days = "+".$days;
        }
        $customers = $this->customerCollectionFactory->create()
            ->joinAttribute('dob', 'customer/dob', 'entity_id');
        $customers->getSelect()->where('extract(month from `at_dob`.`dob`) = ?', $this->date->date('m'))
            ->where('extract(day from `at_dob`.`dob`) = ?', $this->date->date('d', ' '.$days.' days'));
        foreach ($customers as $customer) {
            $customerId = $customer->getId();
            $transactionNote = __("Reward point on birthday");
            $rewardData = [
                'customer_id' => $customerId,
                'points' => $rewardPoints,
                'type' => 'credit',
                'review_id' => 0,
                'order_id' => 0,
                'status' => 1,
                'note' => $transactionNote
            ];
            $msg = __(
                'You got %1 reward points on birthday',
                $rewardPoints
            )->render();
            $adminMsg = __(
                ' have got %1 reward points on birthday',
                $rewardPoints
            )->render();
            $helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
        }
    }

    /**
     * Send Points Expire Email
     *
     * @return void
     */
    public function sendPointsExpireEmail()
    {
        $helper = $this->helper;
        $days = $helper->getConfigData('send_before_expiring_days');
        $date = $this->date->gmtDate('Y-m-d', time() + 60 * 60 * 24 * $days);
        $transactions = $this->rewarddetail->create()->getCollection()
                ->addFieldToFilter('expires_at', ['like' => $date.'%'])
                ->addFieldToFilter('is_expired', 0)
                ->addFieldToFilter('is_expiration_email_sent', 0)
                ->addFieldToFilter('action', 'credit');
        $transactions->getSelect()->where('reward_point > reward_used OR reward_used IS NULL');
        foreach ($transactions as $transaction) {
            $helper->sendPointsExpireEmail($transaction);
            $transaction->setIsExpirationEmailSent(1)
                        ->save();
        }
    }
}
