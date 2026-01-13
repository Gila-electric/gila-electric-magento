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

use Webkul\RewardSystem\Model\ResourceModel\ReferralDetail\CollectionFactory as ReferralDetailCollectionFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;

class ReferralPoints extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ReferralDetailCollectionFactory
     */
    protected $referralDetailCollectionFactory;
    /**
     * @var referralDetailCollection
     */
    protected $referralDetailCollection;
    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

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
     * @param ReferralDetailCollectionFactory $referralDetailCollectionFactory
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $jsonData
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ReferralDetailCollectionFactory $referralDetailCollectionFactory,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerRepositoryInterface $customerRepository,
        Data $jsonData,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->referralDetailCollectionFactory = $referralDetailCollectionFactory;
        $this->rewardHelper = $rewardHelper;
        $this->pricingHelper = $pricingHelper;
        $this->customerRepository = $customerRepository;
        $this->filterProvider = $filterProvider;
        $this->jsonData = $jsonData;
    }

    /**
     * Prepare Layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getReferralDetailCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'rewardsystem.referraldetail.pager'
            )
            ->setCollection(
                $this->getReferralDetailCollection()
            );
            $this->setChild('pager', $pager);
            $this->getReferralDetailCollection()->load();
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
     * Get customer's total referrals and total points earned
     *
     * @param object $referralPointCollection
     */
    public function getReferalPointsInfo($referralPointCollection)
    {
        $totalReferrals = 0;
        $totalPointsEarned = 0;
        $transactions = $referralPointCollection;
        if ($transactions->getSize()) {
            foreach ($transactions as $transaction) {
                $rewardPoints = $transaction->getCustomerRewardPoint();
                if ($rewardPoints) {
                    $totalPointsEarned = $totalPointsEarned + $rewardPoints;
                }
                $totalReferrals++;
            }
        }
        return [$totalReferrals, $totalPointsEarned];
    }

    /**
     * Get referral detail collection of a customer
     *
     * @param int $customerId
     */
    public function getReferralDetailCollection($customerId = null)
    {
        if (!$this->referralDetailCollection) {
            if (!$customerId) {
                $customerId = $this->rewardHelper->getCustomerId();
            }
            $referralDetailCollection = $this->referralDetailCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);
            $this->referralDetailCollection = $referralDetailCollection;
        }
        return $this->referralDetailCollection;
    }

    /**
     * Function getReferralUrl
     *
     * @param int $customerId
     * @return string
     */
    public function getReferralUrl($customerId)
    {
        return $this->getUrl('customer/account/create', ['referral' => $customerId]);
    }

    /**
     * Function getCustomerData by id
     *
     * @param int $id
     * @return object|null
     */
    public function getCustomerData($id)
    {
        try {
            return $this->customerRepository->getById($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Function to get MonthlyReferralInfo
     *
     * @param int $customerId
     * @return void
     */
    public function getMonthlyReferralInfo($customerId = null)
    {
        if (!$customerId) {
            $customerId = $this->rewardHelper->getCustomerId();
        }
        $referralInfoArray[] = [
            __('Month'),
            __('Total Referrals'),
            __('Earned Reward Points')
        ];
        $todayDate = date('Y-m-d');
        for ($i = 5; $i >= 0; $i--) {
            $time = strtotime("-$i month");
            $month = date('M', $time);
            $startDate = date('Y-m-1', $time);
            $endDate = date('Y-m-t', $time);

            $referralCollection = $this->referralDetailCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('created_at', ['lteq' => $endDate])
                ->addFieldToFilter('created_at', ['gteq' => $startDate]);

            list($totalReferrals, $totalPointsEarned) = $this->getReferalPointsInfo($referralCollection);

            $referralInfoArray[] = [
                $month,
                $totalReferrals,
                $totalPointsEarned
            ];
        }
        return $referralInfoArray;
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
