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

namespace Webkul\RewardSystem\Block\Checkout;

use Webkul\RewardSystem\Helper\Data as RewardHelper;
use Webkul\RewardSystem\Model\Rewardrecord as RewardRecordCollection;
use Magento\Framework\Session\SessionManager;
use Magento\Checkout\Model\Session as CheckoutSesssion;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Reward extends \Magento\Framework\View\Element\Template
{
    /**
     * @var RewardHelper;
     */
    protected $helper;

    /**
     * @var RewardRecordCollection;
     */
    protected $rewardRecordCollection;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CheckoutSesssion
     */
    protected $checkoutSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param RewardHelper                                     $helper
     * @param RewardRecordCollection                           $rewardRecordCollection
     * @param SessionManager                                   $session
     * @param CheckoutSesssion                                 $checkoutSession
     * @param PriceCurrencyInterface                           $priceCurrency
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RewardHelper $helper,
        RewardRecordCollection $rewardRecordCollection,
        SessionManager $session,
        CheckoutSesssion $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->rewardRecordCollection = $rewardRecordCollection;
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->priceCurrency = $priceCurrency;
    }

   /**
    * To check the status of module.
    *
    * @return bool
    */
    public function checkModuleStatus()
    {
        $customerId = $this->helper->getCustomerId();
        $configEnable = $this->helper->enableRewardSystem();
        if ($customerId && $configEnable) {
            return true;
        }
    }
    
    /**
     * Get Helper Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Reward Data of customer
     */
    public function getCustomerRewardData()
    {
        $customerId = $this->helper->getCustomerId();
        $reward = 0;
        $rewardData = $this->rewardRecordCollection->getCollection()->addFieldToFilter('customer_id', $customerId);
        foreach ($rewardData as $recordData) {
            $reward = $recordData->getRemainingRewardPoint();
        }
        return $reward;
    }

    /**
     * Get Reward Value Which we set in admin panel
     */
    public function rewardValue()
    {
        $rewardValue = $this->helper->getRewardValue();
        $store = $this->getStore();
        return  $this->priceCurrency->convert(
            $rewardValue,
            $store
        );
    }

    /**
     * Get Currency Symbol for Different Currency
     */
    public function getCheckoutCurrencySymbol()
    {
        $store = $this->getStore();
        return $this->priceCurrency->getCurrencySymbol($store);
    }

    /**
     * Get currenct store
     */
    public function getStore()
    {
        return $this->checkoutSession->getQuote()->getStore();
    }

    /**
     * Get Applied Reward Point By Customer On Cart Page
     */
    public function getAppliedRewardPoint()
    {
        $quote = $this->checkoutSession->getQuote();
        $rewardInfo = $this->helper->getRewardInfoFromQuote($quote);
        return $rewardInfo;
    }
}
