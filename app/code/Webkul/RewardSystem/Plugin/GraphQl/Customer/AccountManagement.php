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

namespace Webkul\RewardSystem\Plugin\GraphQl\Customer;

use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;

class AccountManagement
{
    /**
     * @var RewardSystemHelper
     */
    protected $rewardSystemHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param RewardSystemHelper $rewardSystemHelper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->rewardSystemHelper = $rewardSystemHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * After createAccount
     *
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param object $result
     * @return object
     */
    public function afterCreateAccount(
        \Magento\Customer\Model\AccountManagement $subject,
        $result
    ) {
        $customer = $result;
        if ($customer && $customer->getId()) {
            $helper = $this->rewardSystemHelper;
            $enableRewardSystem = $helper->enableRewardSystem();
            if ($helper->getAllowRegistration() && $enableRewardSystem && $helper->getRewardOnRegistration()) {
                $customerId = $customer->getId();
                $transactionNote = __("Reward point on registration");
                $rewardValue = $helper->getRewardValue();
                $rewardPoints = $helper->getRewardOnRegistration();
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
                    'You got %1 reward points on registration',
                    $rewardPoints
                )->render();
                $adminMsg = __(
                    ' have registered on your site, and got %1 reward points',
                    $rewardPoints
                )->render();
                $helper->setDataFromAdmin(
                    $msg,
                    $adminMsg,
                    $rewardData
                );
            }
        }
        return $result;
    }
}
