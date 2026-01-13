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

namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Webkul\RewardSystem\Api\Data\RewardrecordInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var RewardSystemHelper
     */
    protected $_rewardSystemHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
     /**
      * @var DataObjectHelper
      */
    protected $_dataObjectHelper;

    /**
     * @var RewardrecordInterfaceFactory
     */
    protected $_rewardRecordInterface;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var RewardrecordRepositoryInterface
     */
    protected $_rewardRecordRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $formdata;

    /**
     * @param RewardSystemHelper                      $rewardSystemHelper
     * @param DataObjectHelper                        $dataObjectHelper
     * @param RewardrecordInterfaceFactory            $rewardRecordInterface
     * @param ManagerInterface                        $messageManager
     * @param RewardrecordRepositoryInterface         $rewardRecordRepository
     * @param DateTime                                $datetime
     * @param CustomerRepositoryInterface             $customerRepository
     * @param \Magento\Framework\App\Request\Http     $formdata
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        DataObjectHelper $dataObjectHelper,
        RewardrecordInterfaceFactory $rewardRecordInterface,
        ManagerInterface $messageManager,
        RewardrecordRepositoryInterface $rewardRecordRepository,
        DateTime $datetime,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Request\Http $formdata
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_rewardRecordInterface = $rewardRecordInterface;
        $this->_messageManager = $messageManager;
        $this->_rewardRecordRepository = $rewardRecordRepository;
        $this->_date = $datetime;
        $this->customerRepository = $customerRepository;
        $this->formdata = $formdata;
    }
    /**
     * Cart save after observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $dob = $this->formdata->getParam('dob');
        if ($dob != null) {
            $customer->setDob($dob);
            $this->customerRepository->save($customer);
        }
        $helper = $this->_rewardSystemHelper;
        $enableRewardSystem = $helper->enableRewardSystem();
        $customerId = $customer->getId();
        if ($helper->getAllowRegistration() && $enableRewardSystem && $helper->getRewardOnRegistration()) {
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
            $this->_messageManager->addSuccess(__(
                'You got %1 reward points on registration',
                $rewardPoints
            ));
        }

        $referralCode = $this->formdata->getParam('referral_code', false);
        if ($helper->isReferralEnabled() && $enableRewardSystem && $referralCode) {
            $helper->processReferralOnRegistration(
                $referralCode,
                $customerId
            );
        }
    }
}
