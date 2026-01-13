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
namespace Webkul\RewardSystem\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManager;

class Rewardamount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator = null;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Event\ManagerInterface  $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteValidator        $quoteValidator
     * @param PriceCurrencyInterface                     $priceCurrency
     * @param \Webkul\RewardSystem\Helper\Data           $rewardHelper
     * @param \Magento\Framework\App\Request\Http        $request
     * @param SessionManager                             $session
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\App\Request\Http $request,
        SessionManager $session
    ) {
        $this->setCode('reward');
        $this->session = $session;
        $this->eventManager = $eventManager;
        $this->quoteValidator = $quoteValidator;
        $this->_rewardHelper = $rewardHelper;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
    }

    /**
     * Collect method
     *
     * @param \Magento\Quote\Model\Quote                          $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total            $total
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $helper = $this->_rewardHelper;
        $address = $shippingAssignment->getShipping()->getAddress();
        $rewardInfo = $helper->getRewardInfoFromQuote($quote);
        $rewardamountAmount = 0;
        $store = $quote->getStore();
        
        if (is_array($rewardInfo)) {
            $multiShipRewardAmount = $this->session->getMultiShipRewardTotal();
            $rewardamountAmount = $rewardamountAmount + $rewardInfo['amount'];
            if ($this->request->getFullActionName()=='multishipping_checkout_addressesPost'
            || $this->request->getRouteName()=='checkout') {
                $this->session->setMultiShipRewardDataSet(false);
            }
            if ($this->request->getModuleName() == 'multishipping' &&
             ($this->request->getFullActionName()=='multishipping_checkout_shippingPost' ||
              $this->session->getMultiShipRewardDataSet())) {
                
                $this->session->setMultiShipRewardDataSet(true);
                $rewardamountAmount = 0;
                $multiShipRewardData = $this->session->getMultiShipRewardData();
                if (!$multiShipRewardData ||
                 !is_array($multiShipRewardData)) {
                    $multiShipRewardData = [];
                }

                if ($multiShipRewardAmount) {
                    if ($this->request->getFullActionName()=='multishipping_checkout_overview') {
                        $actualTotal = $address->getSubtotal()+$address->getTaxAmount() +
                        $address->getShippingAmount()-$address->getDiscountAmount();
                        if (floatval($multiShipRewardAmount)>floatval($actualTotal)) {
                            $rewardamountAmount = $actualTotal;
                        } else {
                            $rewardamountAmount = $multiShipRewardAmount;
                        }
                        $this->session->setMultiShipRewardTotal($multiShipRewardAmount-$rewardamountAmount);
                        $multiShipRewardData[$address->getAddressId().""]=$rewardamountAmount;
                    
                        $this->session->setMultiShipRewardData($multiShipRewardData);
                    }
                    
                } else {
                    if (isset($multiShipRewardData[$address->getAddressId()])) {
                        $rewardamountAmount = $multiShipRewardData[$address->getAddressId()];
                    }
                }
            }
        }
        
        $currentCurrencyCode = $helper->getCurrentCurrencyCode();
        $baseCurrencyCode = $helper->getBaseCurrencyCode();
        $allowedCurrencies = $helper->getConfigAllowCurrencies();
        $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        $rewardamountAmount = $helper->currentCurrencyAmount($rewardamountAmount);
        $baserewardamountAmount = $helper->baseCurrencyAmount($rewardamountAmount);
        $baserewardamountAmount = -($baserewardamountAmount);
        $rewardamountAmount = -($rewardamountAmount);

        $address->setData('reward_amount', $rewardamountAmount);
        $address->setData('base_reward_amount', $baserewardamountAmount);
        $total->setTotalAmount('reward', $rewardamountAmount);
        $total->setBaseTotalAmount('reward', $baserewardamountAmount);
        $quote->setRewardAmount($rewardamountAmount);
        $quote->setBaseRewardAmount($baserewardamountAmount);
        $total->setRewardAmount($rewardamountAmount);
        $total->setBaseRewardAmount($baserewardamountAmount);

        return $this;
    }

    /**
     * Add shipping totals information to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $title = __('Rewarded Amount');
        return [
            'code'  => $this->getCode(),
            'title' => $title,
            'value' => $total->getRewardAmount()
        ];
    }

    /**
     * Get Shipping label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Rewarded Amount');
    }
}
