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
namespace Webkul\RewardSystem\Model;

use Magento\Framework\Session\SessionManager;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Magento\Checkout\Model\Session;

class RewardRepository implements \Webkul\RewardSystem\Api\RewardRepositoryInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var RewardSystemHelper
     */
    protected $_rewardSystemHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $chkoutSession;

    /**
     * @param RewardSystemHelper                          $rewardSystemHelper
     * @param SessionManager                              $session
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\App\Request\Http         $request
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Session                                     $chkoutSession
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        SessionManager $session,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Session $chkoutSession
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->session = $session;
        $this->request = $request;
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->chkoutSession = $chkoutSession;
    }

    /**
     * Save Credit.
     *
     * @param \Webkul\RewardSystem\Api\RewardRepositoryInterface $rewardData
     *
     * @return array $rewardInfo
     *
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     */
    public function save($rewardData)
    {
        $fieldValues = $this->request->getParams();
        $helper = $this->_rewardSystemHelper;
        $quote = $this->chkoutSession->getQuote();
        if (isset($fieldValues['cancel'])) {
            $helper->unsetRewardInfoInQuote($quote);
            return [];
        }
        $customerRewards = $fieldValues['number_of_rewards'];
        $maxRewardUsed = $helper->getRewardCanUsed();
        if ($fieldValues['used_reward_points'] > $customerRewards) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You don\'t have sufficient Reward Points to use.')
            );
        }
        if ($fieldValues['used_reward_points'] > $maxRewardUsed) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can not use more than %1 reward points for this order purchase.', $maxRewardUsed)
            );
        }
        $grandTotal = $quote->getGrandTotal()-$quote->getRewardAmount();
        $baseGrandTotal = $quote->getBaseGrandTotal();
        $perRewardAmount = $helper->getRewardValue();
        $rewardAmount = $fieldValues['used_reward_points']*$perRewardAmount;
        if ($baseGrandTotal >= $rewardAmount) {
            $flag = 0;
            $amount = 0;
            $availAmount = $customerRewards * $perRewardAmount;
            $rewardInfo = $helper->getRewardInfoFromQuote($quote);
            if (!$rewardInfo) {
                $amount = $rewardAmount;
                $rewardInfo = [];
                $rewardInfo = [
                   'used_reward_points' => $fieldValues['used_reward_points'],
                   'number_of_rewards' => $customerRewards,
                   'avail_amount' => $availAmount,
                   'amount' => $amount
                ];
            } else {
                if (is_array($rewardInfo)) {
                    $rewardInfo['used_reward_points'] = $fieldValues['used_reward_points'];
                    $rewardInfo['number_of_rewards'] = $customerRewards;
                    $rewardInfo['avail_amount'] = $availAmount;
                    $amount = $rewardAmount;
                    $rewardInfo['amount'] = $amount;

                    $flag = 1;
                }
                if ($flag == 0) {
                    $amount = $rewardAmount;
                    $rewardInfo= [
                       'used_reward_points' => $fieldValues['used_reward_points'],
                       'number_of_rewards' => $customerRewards,
                       'avail_amount' => $availAmount,
                       'amount' => $amount
                    ];
                }
            }
            $helper->setRewardInfoInQuote($quote, $rewardInfo);
            if (isset($rewardInfo['amount'])) {
                $rewardInfo['amount'] = $helper->currentCurrencyAmount($rewardInfo['amount']);
            }
            if (isset($rewardInfo['avail_amount'])) {
                $rewardInfo['avail_amount'] = $helper->currentCurrencyAmount($rewardInfo['avail_amount']);
            }

        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Reward Amount can not be greater than Order Total.')
            );
        }
        $rewardsInfo[] =$rewardInfo;
        return $rewardsInfo;
    }
}
