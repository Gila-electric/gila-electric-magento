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
declare(strict_types=1);

namespace Webkul\RewardSystem\Model\Resolver\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

use Magento\Framework\Session\SessionManager;
use Webkul\RewardSystem\Model\RewardrecordFactory as RewardRecordCollection;
use Webkul\RewardSystem\Helper\Data as RewardHelper;

/**
 * @inheritdoc
 */
class ApplyRewards implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;
    
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RewardRecordCollection;
     */
    protected $rewardRecordCollection;

    /**
     * @var RewardHelper;
     */
    protected $helper;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SessionManager $session
     * @param RewardRecordCollection $rewardRecordCollection
     * @param RewardHelper $helper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SessionManager $session,
        RewardRecordCollection $rewardRecordCollection,
        RewardHelper $helper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->session = $session;
        $this->rewardRecordCollection = $rewardRecordCollection;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['used_reward_points'])) {
            throw new GraphQlInputException(__('Required parameter "used_reward_points" is missing'));
        }
        $fieldValues = $args['input'];

        $customerId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $helper = $this->helper;
        $quote = $this->getCartForUser->execute($maskedCartId, $customerId, $storeId);
        $totalRewards = $this->getRewardData($customerId, $quote);

        if ($fieldValues['used_reward_points'] > $totalRewards['remaining_rewards']) {
            throw new GraphQlInputException(
                __('Reward points can\'t be greater than customer\'s reward point(s).')
            );
        }
        /**
         * How much reward point can be used of customer
         */
        $maxRewardUsed = $helper->getRewardCanUsed();
        if ($fieldValues['used_reward_points'] > $maxRewardUsed) {
            throw new LocalizedException(
                __(
                    'You can not use more than %1 reward points for this order purchase.',
                    $maxRewardUsed
                )
            );
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
            throw new GraphQlInputException(
                __('Reward Amount can not be greater than Order Total.')
            );
    
        }

        return [
            'cart' => [
                'model' => $quote,
            ],
        ];
    }

    /**
     * Get Reward Data of customer
     *
     * @param int $customerId
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    public function getRewardData($customerId, $quote)
    {
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
