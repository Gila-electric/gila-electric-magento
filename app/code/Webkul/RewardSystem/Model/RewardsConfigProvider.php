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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;

class RewardsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_cart;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var RewardrecordFactory
     */
    protected $_rewardRules;
    /**
     * @var RewardSystemHelper
     */
    protected $_rewardSystemHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param RewardSystemHelper                        $rewardSystemHelper
     * @param CurrentCustomer                           $currentCustomer
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RewardrecordFactory                       $rewardRules
     * @param SessionManager                            $session
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param \Magento\Framework\UrlInterface           $urlBuilder
     * @param \Magento\Checkout\Model\Session           $cart
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        CurrentCustomer $currentCustomer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RewardrecordFactory $rewardRules,
        SessionManager $session,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $cart
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_objectManager = $objectManager;
        $this->currentCustomer = $currentCustomer;
        $this->session = $session;
        $this->_rewardRules = $rewardRules;
        $this->_priceCurrency = $priceCurrency;
        $this->_urlBuilder = $urlBuilder;
        $this->_cart = $cart;
    }

    /**
     * Set data in window.checkout.config for checkout page.
     *
     * @return array $options
     */
    public function getConfig()
    {
        $options = [
            'rewards' => [],
            'rewardSession'=> [],
            'rewardMessage' => []
        ];
        $helper = $this->_rewardSystemHelper;
        $enableRewardSystem = $helper->enableRewardSystem();

        $quote = $this->_cart->getQuote();
        $store = $this->_cart->getQuote()->getStore();
        $customerId = $helper->getCustomerId();
        $rewardInfo = $helper->getRewardInfoFromQuote($quote);
        if (is_array($rewardInfo)) {
            $options['rewardSession'] = $rewardInfo;
            $options['rewardSession']['base_amount'] = $options['rewardSession']['amount'];
            $amountPrice = $this->_priceCurrency->convert(
                $options['rewardSession']['amount'],
                $store
            );
            $availAmount = $this->_priceCurrency->convert(
                $options['rewardSession']['avail_amount'],
                $store
            );
            $options['rewardSession']['amount'] = $amountPrice;
            $options['rewardSession']['avail_amount'] = $availAmount;
        }
        $collection = $this->_rewardRules->create()
                        ->getCollection()
                        ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($collection as $info) {
            if (!isset($options['rewards']['total_reward_point'])) {
                $options['rewards'] = [];
                $totalRewardPoint = $info->getRemainingRewardPoint();
            } else {
                $totalRewardPoint = $options['rewards']['total_reward_point'] +
                $info->getRemainingRewardPoint();
            }
            $amount = $totalRewardPoint * $helper->getRewardValue();
            //conver currency according to store
            $amountPrice = $this->_priceCurrency->convert(
                $amount,
                $store
            );
            $pricePerReward = $helper->getRewardValue();
            $pricePerReward = $this->_priceCurrency->convert(
                $pricePerReward,
                $store
            );
            //create array of rewards for display on checkout page
            $options['rewards']['total_reward_point'] = $totalRewardPoint;
            $options['rewards']['currency'] = $this->_priceCurrency->getCurrencySymbol($store);
            $options['rewards']['amount'] = $this->_priceCurrency->getCurrencySymbol($store).$pricePerReward;
            $options['rewards']['point_amount'] = $pricePerReward;
            $options['rewards']['status'] = $enableRewardSystem;
        }
        if (!$customerId) {
            //create array for Guest User Rewards Message On checkout page
            $options['rewardMessage']['status'] = $enableRewardSystem ?? $helper->getAllowRegistration();
            $options['rewardMessage']['total_reward_point'] = $helper->getRewardOnRegistration();
            $options['rewardMessage']['url'] = $this->_urlBuilder->getUrl('customer/account/create');
        }
        return $options;
    }
}
