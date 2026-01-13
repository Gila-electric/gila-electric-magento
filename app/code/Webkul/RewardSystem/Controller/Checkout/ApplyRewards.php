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
namespace Webkul\RewardSystem\Controller\Checkout;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManager;
use Webkul\RewardSystem\Model\RewardrecordFactory as RewardRecordCollection;
use Webkul\RewardSystem\Helper\Data as RewardHelper;

class ApplyRewards extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var RewardRecordCollection;
     */
    protected $rewardRecordCollection;

    /**
     * @var RewardHelper;
     */
    protected $helper;

    /**
     * @param Context                                    $context
     * @param SessionManager                             $session
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Magento\Checkout\Model\Session            $customerSession
     * @param RewardRecordCollection                     $rewardRecordCollection
     * @param RewardHelper                               $helper
     * @param array                                      $data
     */
    public function __construct(
        Context $context,
        SessionManager $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        RewardRecordCollection $rewardRecordCollection,
        RewardHelper $helper,
        array $data = []
    ) {
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->rewardRecordCollection = $rewardRecordCollection;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $helper = $this->helper;
        $fieldValues = $this->getRequest()->getParams();
        $quote = $this->checkoutSession->getQuote();
        $totalRewards = $this->getRewardData();
        
        $customerId = $helper->getCustomerId();
        if ($fieldValues['used_reward_points'] > $totalRewards['remaining_rewards']) {
            $this->messageManager->addError(__('Reward points can\'t be greater than customer\'s reward point(s).'));
            return $this->resultRedirectFactory
                ->create()
                ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
        }
        /**
         * How much reward point can be used of customer
         */
        $maxRewardUsed = $helper->getRewardCanUsed();
        if ($fieldValues['used_reward_points'] > $maxRewardUsed) {
            $this->messageManager->addError(
                __(
                    'You can not use more than %1 reward points for this order purchase.',
                    $maxRewardUsed
                )
            );
            return $this->resultRedirectFactory
                ->create()
                ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
        }
        
        $grandTotal = $quote->getGrandTotal();
        $baseGrandTotal = $quote->getBaseGrandTotal();
        $perRewardAmount = $helper->getRewardValue();
        $rewardAmount = $fieldValues['used_reward_points'] * $perRewardAmount;
        if ($baseGrandTotal >= $rewardAmount) {
            $flag = 0;
            $amount = 0;
            $availAmount = $totalRewards['amount'];
            $rewardInfo = $helper->getRewardInfoFromQuote($quote);
            if (!$rewardInfo) {
                $amount = $rewardAmount;
                $rewardInfo = [
                   'used_reward_points' => $fieldValues['used_reward_points'],
                   'number_of_rewards' => $totalRewards['remaining_rewards'],
                   'avail_amount' => $availAmount,
                   'amount' => $amount
                ];
            } else {
                if (is_array($rewardInfo)) {
                    $rewardInfo['used_reward_points'] = $fieldValues['used_reward_points'];
                    $rewardInfo['number_of_rewards'] = $totalRewards['remaining_rewards'];
                    $rewardInfo['avail_amount'] = $availAmount;
                    $amount = $rewardAmount;
                    $rewardInfo['amount'] = $amount;

                    $flag = 1;
                }
                if ($flag == 0) {
                    $amount = $rewardAmount;
                    $rewardInfo= [
                       'used_reward_points' => $fieldValues['used_reward_points'],
                       'number_of_rewards' => $totalRewards['remaining_rewards'],
                       'avail_amount' => $availAmount,
                       'amount' => $amount
                    ];
                }
            }

            $helper->setRewardInfoInQuote($quote, $rewardInfo);
            $this->session->setMultiShipRewardTotal($amount);
        } else {
            $this->messageManager->addError(__('Reward Amount can not be greater than Order Total.'));
            return $this->resultRedirectFactory
                ->create()
                ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
        }
        $this->messageManager->addSuccess('Reward has been applied successfully.');
        return $this->resultRedirectFactory
            ->create()
            ->setPath('checkout/cart', ['_secure' => $this->getRequest()->isSecure()]);
    }
    /**
     * Get Reward Data of customer
     */
    public function getRewardData()
    {
        $customerId = $this->helper->getCustomerId();
        $quote = $this->checkoutSession->getQuote();
        $options = [];
        $collection = $this->rewardRecordCollection->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'customer_id',
                            ['eq' => $customerId]
                        )->getFirstItem();
        $remainingRewards = $collection->getRemainingRewardPoint();
        $options['remaining_rewards'] = $remainingRewards;
        $options['amount'] = $remainingRewards * $this->helper->getRewardValue();
        $options['customer_id'] = $customerId;
        return $options;
    }
}
