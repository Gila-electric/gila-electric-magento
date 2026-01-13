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

namespace Webkul\RewardSystem\Helper;

use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
use Webkul\RewardSystem\Api\RewarddetailRepositoryInterface;
use Webkul\RewardSystem\Api\RewardproductRepositoryInterface;
use Webkul\RewardSystem\Api\RewardproductSpecificRepositoryInterface;
use Webkul\RewardSystem\Api\RewardcategoryRepositoryInterface;
use Webkul\RewardSystem\Api\ReferralDetailRepositoryInterface;
use Webkul\RewardSystem\Api\Data\RewardrecordInterfaceFactory;
use Webkul\RewardSystem\Api\Data\RewarddetailInterfaceFactory;
use Webkul\RewardSystem\Api\Data\RewardproductInterfaceFactory;
use Webkul\RewardSystem\Api\Data\RewardproductSpecificInterfaceFactory;
use Webkul\RewardSystem\Api\Data\RewardcategoryInterfaceFactory;
use Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterfaceFactory;
use Webkul\RewardSystem\Api\Data\ReferralDetailInterfaceFactory;
use Webkul\RewardSystem\Api\RewardcategorySpecificRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory as RewardRecordCollection;
use Webkul\RewardSystem\Model\ResourceModel\Rewardproduct\CollectionFactory as RewardProductCollection;
use Webkul\RewardSystem\Model\ResourceModel\RewardproductSpecific\CollectionFactory as RewardproductSpecificCollection;
use Webkul\RewardSystem\Model\ResourceModel\Rewardcart\CollectionFactory as RewardcartCollection;
use Webkul\RewardSystem\Model\ResourceModel\Rewardcategory\CollectionFactory as RewardcategoryCollection;
use Webkul\RewardSystem\Model\ResourceModel\RewardcategorySpecific\CollectionFactory
    as RewardcategorySpecificCollection;
use Webkul\RewardSystem\Model\ResourceModel\ReferralDetail\CollectionFactory as ReferralDetailCollectionFactory;
use Webkul\RewardSystem\Model\ResourceModel\Rewardattribute\CollectionFactory as RewardattributeCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $_orderModel;

    /**
     * @var \Webkul\RewardSystem\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartModel;

    /**
     * @var \Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
     */
    protected $_rewardRecordRepository;

    /**
     * @var \Webkul\RewardSystem\Api\RewarddetailRepositoryInterface;
     */
    protected $_rewardDetailRepository;

    /**
     * @var \Webkul\RewardSystem\Api\RewardproductRepositoryInterface;
     */
    protected $_rewardProductRepository;

    /**
     * @var \Webkul\RewardSystem\Api\RewardproductSpecificRepositoryInterface;
     */
    protected $_rewardproductSpecificRepository;

    /**
     * @var \Webkul\RewardSystem\Api\RewardcategoryRepositoryInterface;
     */
    protected $_rewardCategoryRepository;

    /**
     * @var \Webkul\RewardSystem\Api\Data\RewardrecordInterfaceFactory;
     */
    protected $_rewardRecordInterface;

    /**
     * @var \Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
     */
    protected $_rewardDetailInterface;

    /**
     * @var \Webkul\RewardSystem\Api\Data\RewardproductInterfaceFactory;
     */
    protected $_rewardProductInterface;

    /**
     * @var \Webkul\RewardSystem\Api\Data\RewardproductSpecificInterfaceFactory;
     */
    protected $_rewardproductSpecificInterface;

    /**
     * @var \Webkul\RewardSystem\Api\Data\RewardcategoryInterfaceFactory;
     */
    protected $_rewardCategoryInterface;

    /**
     * @var \Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterfaceFactory;
     */
    protected $_rewardcategorySpecificInterface;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var RewardProductCollection;
     */
    protected $_rewardProductCollection;

    /**
     * @var RewardRecordCollection;
     */
    protected $_rewardRecordCollection;

    /**
     * @var RewardcartCollection;
     */
    protected $_rewardcartCollection;

    /**
     * @var RewardattributeCollection;
     */
    protected $_rewardattributeCollection;

    /**
     * @var RewardcategoryCollection;
     */
    protected $_rewardcategoryCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\App\Http\Context;
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\App\Cache\ManagerFactory
     */
    protected $cacheManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\RewardSystem\Model\RewardorderDetailFactory
     */
    protected $_RewardorderDetailFactory;

    /**
     * @var \Webkul\RewardSystem\Model\RewardproductFactory $RewardproductFactory
     */
    protected $_RewardproductFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var RewardcategorySpecificRepositoryInterface
     */
    protected $_rewardcategorySpecificRepository;

    /**
     * @var RewardproductSpecificCollection
     */
    protected $_rewardproductSpecificCollection;

    /**
     * @var RewardcategorySpecificCollection
     */
    protected $_rewardcategorySpecificCollection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var ReferralDetailRepositoryInterface
     */
    protected $referralDetailRepository;

    /**
     * @var ReferralDetailInterfaceFactory
     */
    protected $referralDetailFactory;

    /**
     * @var ReferralDetailCollectionFactory
     */
    protected $referralDetailCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param OrderFactory $orderModel
     * @param \Webkul\RewardSystem\Helper\Mail $mailHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param RewardrecordRepositoryInterface $rewardRecordRepository
     * @param RewarddetailRepositoryInterface $rewardDetailRepository
     * @param RewardproductRepositoryInterface $rewardProductRepository
     * @param RewardproductSpecificRepositoryInterface $rewardproductSpecificRepository
     * @param RewardcategoryRepositoryInterface $rewardCategoryRepository
     * @param RewardcategorySpecificRepositoryInterface $rewardcategorySpecificRepository
     * @param RewardrecordInterfaceFactory $rewardRecordInterface
     * @param RewarddetailInterfaceFactory $rewardDetailInterface
     * @param RewardproductInterfaceFactory $rewardProductInterface
     * @param RewardproductSpecificInterfaceFactory $rewardproductSpecificInterface
     * @param RewardcategoryInterfaceFactory $rewardCategoryInterface
     * @param RewardcategorySpecificInterfaceFactory $rewardcategorySpecificInterface
     * @param DataObjectHelper $dataObjectHelper
     * @param RewardRecordCollection $rewardRecordCollection
     * @param RewardProductCollection $rewardProductCollection
     * @param RewardproductSpecificCollection $rewardproductSpecificCollection
     * @param RewardcartCollection $rewardcartCollection
     * @param RewardattributeCollection $rewardattributeCollection
     * @param RewardcategorySpecificCollection $rewardcategorySpecificCollection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param RewardcategoryCollection $rewardcategoryCollection
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Webkul\RewardSystem\Model\RewardorderDetailFactory $RewardorderDetailFactory
     * @param \Webkul\RewardSystem\Model\RewardproductFactory $RewardproductFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ReferralDetailRepositoryInterface $referralDetailRepository
     * @param ReferralDetailInterfaceFactory $referralDetailFactory
     * @param ReferralDetailCollectionFactory $referralDetailCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        OrderFactory $orderModel,
        \Webkul\RewardSystem\Helper\Mail $mailHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        RewardrecordRepositoryInterface $rewardRecordRepository,
        RewarddetailRepositoryInterface $rewardDetailRepository,
        RewardproductRepositoryInterface $rewardProductRepository,
        RewardproductSpecificRepositoryInterface $rewardproductSpecificRepository,
        RewardcategoryRepositoryInterface $rewardCategoryRepository,
        RewardcategorySpecificRepositoryInterface $rewardcategorySpecificRepository,
        RewardrecordInterfaceFactory $rewardRecordInterface,
        RewarddetailInterfaceFactory $rewardDetailInterface,
        RewardproductInterfaceFactory $rewardProductInterface,
        RewardproductSpecificInterfaceFactory $rewardproductSpecificInterface,
        RewardcategoryInterfaceFactory $rewardCategoryInterface,
        RewardcategorySpecificInterfaceFactory $rewardcategorySpecificInterface,
        DataObjectHelper $dataObjectHelper,
        RewardRecordCollection $rewardRecordCollection,
        RewardProductCollection $rewardProductCollection,
        RewardproductSpecificCollection $rewardproductSpecificCollection,
        RewardcartCollection $rewardcartCollection,
        RewardattributeCollection $rewardattributeCollection,
        RewardcategorySpecificCollection $rewardcategorySpecificCollection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ProductFactory $productModel,
        RewardcategoryCollection $rewardcategoryCollection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory,
        LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\RewardSystem\Model\RewardorderDetailFactory $RewardorderDetailFactory,
        \Webkul\RewardSystem\Model\RewardproductFactory $RewardproductFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\App\RequestInterface $request,
        ReferralDetailRepositoryInterface $referralDetailRepository,
        ReferralDetailInterfaceFactory $referralDetailFactory,
        ReferralDetailCollectionFactory $referralDetailCollectionFactory
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_localeCurrency = $localeCurrency;
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->_pricingHelper = $pricingHelper;
        $this->_date = $date;
        $this->_orderModel = $orderModel;
        $this->_mailHelper = $mailHelper;
        $this->cartModel = $cartModel;
        $this->_customerModel = $customerFactory;
        $this->_rewardRecordRepository = $rewardRecordRepository;
        $this->_rewardDetailRepository = $rewardDetailRepository;
        $this->_rewardProductRepository = $rewardProductRepository;
        $this->_rewardproductSpecificRepository = $rewardproductSpecificRepository;
        $this->_rewardCategoryRepository = $rewardCategoryRepository;
        $this->_rewardcategorySpecificRepository = $rewardcategorySpecificRepository;
        $this->_rewardRecordInterface = $rewardRecordInterface;
        $this->_rewardDetailInterface = $rewardDetailInterface;
        $this->_rewardProductInterface = $rewardProductInterface;
        $this->_rewardproductSpecificInterface = $rewardproductSpecificInterface;
        $this->_rewardCategoryInterface = $rewardCategoryInterface;
        $this->_rewardcategorySpecificInterface = $rewardcategorySpecificInterface;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_rewardRecordCollection = $rewardRecordCollection;
        $this->_rewardProductCollection = $rewardProductCollection;
        $this->_rewardproductSpecificCollection = $rewardproductSpecificCollection;
        $this->_rewardcartCollection = $rewardcartCollection;
        $this->_rewardattributeCollection = $rewardattributeCollection;
        $this->_rewardcategorySpecificCollection = $rewardcategorySpecificCollection;
        $this->eavConfig = $eavConfig;
        $this->productModel = $productModel;
        $this->httpContext = $httpContext;
        $this->_rewardcategoryCollection = $rewardcategoryCollection;
        $this->timezone = $timezone;
        $this->cacheManager = $cacheManagerFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->_RewardorderDetailFactory = $RewardorderDetailFactory;
        $this->_RewardproductFactory = $RewardproductFactory;
        $this->json = $json;
        $this->request = $request;
        $this->referralDetailRepository = $referralDetailRepository;
        $this->referralDetailFactory = $referralDetailFactory;
        $this->referralDetailCollectionFactory = $referralDetailCollectionFactory;
    }

    /**
     * Return customer id from customer session
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }

    /**
     * Get Reward configurations value
     *
     * @param string $field
     */
    public function getConfigData($field)
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/'.$field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Enable Reward System
     */
    public function enableRewardSystem()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reward points value
     */
    public function getRewardValue()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/reward_value',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return maximum reward points can assign
     */
    public function getRewardCanAssign()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/max_reward_assign',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return maximum reward points can use
     */
    public function getRewardCanUsed()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/max_reward_used',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return either reward points allowed on registration or not
     */
    public function getAllowRegistration()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/allow_registration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reward points on registraion
     */
    public function getRewardOnRegistration()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/registration_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return either reward points allowed on registration or not
     */
    public function getAllowReview()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/allow_review',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reward points on review
     */
    public function getRewardOnReview()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/review_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reward priority set in system config
     */
    public function getrewardPriority()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/priority',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get status of product quantity wise reward
     */
    public function getrewardQuantityWise()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/activeproduct',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Default Transaction Email Id
     */
    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Default Transaction Name
     */
    public function getDefaultTransName()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Reward Approved On
     */
    public function getRewardApprovedOn()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/general_settings/order_reward_approved_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Reward Info Enable status
     */
    public function isRewardInfoEnabled()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Reward Information Content
     */
    public function getRewardInfoContent()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/content',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Get RewardPageTitle
     */
    public function getRewardPageTitle()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/pagetitle',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get DisplayBanner
     */
    public function getDisplayBanner()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/displaybanner',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get BannerImage
     */
    public function getBannerImage()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).'rewardsystem/page/'.$this->scopeConfig->getValue(
            'rewardsystem/reward_information/banner',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get BannerRectangle
     */
    public function getBannerRectangle()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).'rewardsystem/page/'.'reward-page-rectangle.png';
    }

    /**
     * Get getBannerHeading
     */
    public function getBannerHeading()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/bannerheading',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Get getBannerText
     */
    public function getBannerText()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/bannertext',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Get BannerContent
     */
    public function getRewardPageLabel1()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/pagelabel1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Get RewardPageContent
     */
    public function getRewardPageContent()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/reward_information/pagecontent',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: '';
    }

    /**
     * Get BannerImage
     */
    public function getRewardFlowImage()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).'rewardsystem/page/'.$this->scopeConfig->getValue(
            'rewardsystem/reward_information/flowimage',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Referral Program Enable status
     */
    public function isReferralEnabled()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/referral/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reward points for customer on referral
     */
    public function getCustomerRewardOnReferral()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/referral/customer_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reward points for referee on referral
     */
    public function getRefereeRewardOnReferral()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/referral/referee_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Referral Information Content
     */
    public function getReferralInfoContent()
    {
        return $this->scopeConfig->getValue(
            'rewardsystem/referral/content',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format Date
     *
     * @param string $date
     * @param string $format
     * @return string Formatted Date
     */
    public function formatDate($date, $format = \IntlDateFormatter::FULL)
    {
        if ($date) {
            return $this->timezone->formatDate(
                $date,
                $format,
                false
            );
        } else {
            return __("-");
        }
    }

    /**
     * Get Time According To TimeZone Magento Locale Timezone
     *
     * @param string $dateTime
     */
    public function getTimeAccordingToTimeZone($dateTime)
    {
        // for get current time according to time zone
        $today = $this->timezone->date()->format('h:i A');

        // for convert date time according to magento time zone
        $dateTimeAsTimeZone = $this->timezone
                                        ->date(new \DateTime($dateTime))
                                       ->format('h:i A');
        return $dateTimeAsTimeZone;
    }

    /**
     * Save Data Reward Record
     *
     * @param object $completeDataObject
     */
    public function saveDataRewardRecord($completeDataObject)
    {
        try {
            $this->_rewardRecordRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Save Data Reward Detail
     *
     * @param object $completeDataObject
     */
    public function saveDataRewardDetail($completeDataObject)
    {
        try {
            $this->_rewardDetailRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Return currency currency code
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get base currency code
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get all allowed currency in system config
     */
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    /**
     * Get currency rates
     *
     * @param string $currency
     * @param string $toCurrencies
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * Get currency symbol of an currency code
     *
     * @param string $currencycode
     */
    public function getCurrencySymbol($currencycode)
    {
        $currency = $this->_localeCurrency->getCurrency($currencycode);

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * Get formatted Price
     *
     * @param int $price
     */
    public function getformattedPrice($price)
    {
        return $this->_pricingHelper
            ->currency($price, true, false);
    }

    /**
     * Get Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Get currenct currency amount from base
     *
     * @param int $amount
     * @param string $store
     */
    public function currentCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        $returnAmount = $this->_priceCurrency->convert($amount, $store);

        return round($returnAmount, 4);
    }

    /**
     * Get amount in base currency amount from current currency
     *
     * @param int $amount
     * @param string $store
     */
    public function baseCurrencyAmount($amount, $store = null)
    {
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        if ($amount == 0) {
            return $amount;
        }
        $rate = $this->_priceCurrency->convert($amount, $store) / $amount;
        $amount = $amount / $rate;

        return round($amount, 4);
    }

    /**
     * Get customer's Remaining Reward Points
     *
     * @param int $customerId
     */
    public function getCurrentRewardOfCustomer($customerId)
    {
        $reward = 0;
        $rewardRecordCollection = $this->_rewardRecordCollection->create()
          ->addFieldToFilter('customer_id', $customerId);
        if ($rewardRecordCollection->getSize()) {
            foreach ($rewardRecordCollection as $recordData) {
                $reward = $recordData->getRemainingRewardPoint();
            }
        }
        return $reward;
    }

    /**
     * Get customer's Remaining Reward Points
     *
     * @param int $customerId
     */
    public function getRewardRecordOfCustomer($customerId)
    {
        $rewardRecord = false;
        $rewardRecordCollection = $this->_rewardRecordCollection->create()
          ->addFieldToFilter('customer_id', $customerId);
        if ($rewardRecordCollection->getSize()) {
            foreach ($rewardRecordCollection as $recordData) {
                $rewardRecord = $recordData;
            }
        }
        return $rewardRecord;
    }

    /**
     * Send Points Expire Email
     *
     * @param  \Webkul\RewardSystem\Model\Rewarddetail $transaction
     * @return
     */
    public function sendPointsExpireEmail($transaction)
    {
        $customerId = $transaction->getCustomerId();
        $remainingPoints = $transaction->getRewardPoint() - $transaction->getRewardUsed();
        $msg = __(
            "Please, note that your reward points %1 will expire on %2",
            $remainingPoints,
            $transaction->getExpiresAt()
        )->render();
        $customer = $this->_customerModel
          ->create()
          ->load($customerId);
        $receiverInfo = [
          'name' => $customer->getName(),
          'email' => $customer->getEmail(),
        ];
        $adminEmail= $this->getDefaultTransEmailId();
        $adminUsername = $this->getDefaultTransName();
        $senderInfo = [
          'name' => $adminUsername,
          'email' => $adminEmail,
        ];
        $this->_mailHelper->sendExpireEmail($receiverInfo, $senderInfo, $msg, $remainingPoints);
    }

    /**
     * Set Data From Admin
     *
     * @param string $msg
     * @param string $adminMsg
     * @param array  $rewardData
     */
    public function setDataFromAdmin(
        $msg,
        $adminMsg,
        $rewardData
    ) {
        $assignStatus = true;
        $maxRewardCanAssign = $this->getRewardCanAssign();
        $customerReward = $this->getCurrentRewardOfCustomer($rewardData['customer_id']);
        if ($rewardData['type'] == "credit" && $maxRewardCanAssign < ($customerReward + $rewardData['points'])) {
            $assignStatus = false;
        }
        if ($assignStatus) {
            $status = $rewardData['status'];
            $rewardValue = $this->getRewardValue();
            $baseCurrencyCode = $this->getBaseCurrencyCode();
            $amount = $rewardValue * $rewardData['points'];
            $isRevert = isset($rewardData['is_revert']) ? $rewardData['is_revert']: 0;
            $isExpired = 0;
            if (isset($rewardData['is_expired'])) {
                $isExpired = $rewardData['is_expired'];
            }
            $recordDetail = [
            'customer_id' => $rewardData['customer_id'],
            'reward_point' => $rewardData['points'],
            'amount' => $amount,
            'status' => $status,
            'action' => $rewardData['type'],
            'order_id' => $rewardData['order_id'],
            'transaction_at' => $this->_date->gmtDate(),
            'currency_code' => $baseCurrencyCode,
            'curr_amount' => $amount,
            'review_id' => $rewardData['review_id'],
            'transaction_note' => $rewardData['note'],
            'is_expired' => $isExpired,
            'is_revert' => $isRevert
            ];
            $dataObjectRecordDetail = $this->_rewardDetailInterface->create();

            $this->_dataObjectHelper->populateWithArray(
                $dataObjectRecordDetail,
                $recordDetail,
                \Webkul\RewardSystem\Api\Data\RewarddetailInterface::class
            );
            if ($status==1) {
                $this->updateRewardRecordData($msg, $adminMsg, $rewardData);
                if ($rewardData['type'] == 'debit') {
                    $this->updateExpiryRecordData($rewardData);
                }
            }
            $this->saveDataRewardDetail($dataObjectRecordDetail);
            return [true, 'Successful'];
        } else {
            return [false, 'Total amount exceeds max for some customers.'];
        }
    }

    /**
     * Update Expiry Record Data
     *
     * @param array $rewardData
     */
    public function updateExpiryRecordData($rewardData)
    {
        try {
            $points = $rewardData['points'];
            $customerId = $rewardData['customer_id'];
            $transactions = $this->_rewardDetailInterface->create()
                          ->getCollection()
                          ->addFieldToFilter('customer_id', $customerId)
                          ->addFieldToFilter('is_expired', 0)
                          ->addFieldToFilter('action', 'credit')
                          ->setOrder('expires_at', 'ASC');
            $transactions->getSelect()->where('reward_point > reward_used OR reward_used IS NULL');
            if ($transactions->getSize()) {
                foreach ($transactions as $transaction) {
                    $remainingPoints = $transaction->getRewardPoint() - $transaction->getRewardUsed();
                    if ($points) {
                        if ($points <= $remainingPoints) {
                            $updatedPoints = $transaction->getRewardUsed() + $points;
                            $points = 0;
                        } else {
                            $updatedPoints = $transaction->getRewardUsed() + $remainingPoints;
                            $points -= $remainingPoints;
                        }
                        $transaction->setRewardUsed($updatedPoints)->save();
                    } else {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Update Reward Record Data
     *
     * @param string $msg
     * @param string $adminMsg
     * @param array  $rewardData
     * @param int    $storeId
     */
    public function updateRewardRecordData($msg, $adminMsg, $rewardData, $storeId = 0)
    {
        try {
            $points = $rewardData['points'];
            $customerId = $rewardData['customer_id'];
            $entityId = $this->checkAlreadyExists($customerId);
            $remainingPoints = 0;
            $usedPoints = 0;
            $totalPoints = 0;
            $id = '';
            if ($entityId) {
                $rewardRecord = $this->_rewardRecordRepository->getById($entityId);
                $remainingPoints = $rewardRecord->getRemainingRewardPoint();
                $usedPoints = $rewardRecord->getUsedRewardPoint();
                $totalPoints = $rewardRecord->getTotalRewardPoint();
                $id = $entityId;
            }
            if ($rewardData['type']=='credit') {
                $remainingPoints += $points;
                $totalPoints += $points;
            } else {
                $usedPoints += $points;
                $remainingPoints -= $points;
            }
            if ($remainingPoints<0) {
                throw new LocalizedException(
                    __('Remaining Reward Point can not be less than zero.')
                );
            }
            $recordData = [
                'customer_id' => $customerId,
                'total_reward_point' => $totalPoints,
                'remaining_reward_point' => $remainingPoints,
                'used_reward_point' => $usedPoints,
                'updated_at' => $this->_date->gmtDate()
            ];
            if ($id) {
                $recordData['entity_id'] = $id;
            }

            $dataObjectRewardRecord = $this->_rewardRecordInterface->create();

            $customer = $this->_customerModel
                ->create()
                ->load($customerId);
            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $adminEmail= $this->getDefaultTransEmailId();
            $adminUsername = $this->getDefaultTransName();
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->_dataObjectHelper->populateWithArray(
                $dataObjectRewardRecord,
                $recordData,
                \Webkul\RewardSystem\Api\Data\RewardrecordInterface::class
            );
            $this->saveDataRewardRecord($dataObjectRewardRecord);
            $expiresDays = (int)$this->getConfigData('expires_after_days');
            if (isset($rewardData['reward_id']) && $expiresDays) {
                $date = $this->_date->gmtDate(
                    'Y-m-d',
                    $this->_date->gmtTimestamp() + $expiresDays * 24 * 60 * 60
                );
                $transaction = $this->_rewardDetailInterface->create()
                            ->load($rewardData['reward_id']);
                $transaction->setExpiresAt($date)->save();
            }
            $this->_mailHelper->sendMail($receiverInfo, $senderInfo, $msg, $remainingPoints, $storeId);
            $this->_mailHelper->sendAdminMail($receiverInfo, $senderInfo, $adminMsg, $remainingPoints, $storeId);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Update Reward Order Detail Data
     *
     * @param object $order
     */
    public function updateRewardOrderDetailData($order)
    {
        foreach ($order->getAllVisibleItems() as $_item) {
            $rewardOrderDetail = $this->_RewardorderDetailFactory->create();
            $orderItem = $this->_RewardproductFactory->create()->getCollection()->
            addFieldToFilter('product_id', $_item->getProductId());
            $itemRewardData = $orderItem->getData();
            $itemRewardPoint = $itemRewardData[0]["points"];
            if ($this->getrewardQuantityWise()) {
                $itemRewardPoint *= $_item->getQtyOrdered();
            }
            if ($itemRewardData[0]["status"] == 1) {
                $rewardOrderDetail->addData([
                    "order_id" => $order->getId(),
                    "item_id" => $_item->getProductId(),
                    "points" => $itemRewardPoint,
                    "qty" => $_item->getQtyOrdered(),
                    "is_qty_wise" => $this->getrewardQuantityWise()
                ]);
                $rewardOrderDetail->save();
            }
        }
    }

    /**
     * Get Reward Order Detail Data
     *
     * @param array $order
     * @param array $itemId
     * @param int   $qty
     */
    public function getRewardOrderDetailData($order, $itemId, $qty)
    {
        $reward = $this->_RewardorderDetailFactory->create()->getCollection()->
        addFieldToFilter('order_id', $order->getId());
        $data = $reward->addFieldToFilter('item_id', $itemId)->getData();
        if ($data) {
            if ($data[0]["is_qty_wise"]) {
                $rewardPointPerItem = $data[0]["points"] / $data[0]["qty"];
                $rewardPoint = $rewardPointPerItem * $qty;
                return $rewardPoint;
            } else {
                $rewardPoint = $data[0]["points"];
                return $rewardPoint;
            }
        }
    }

    /**
     * Check Already Exists
     *
     * @param int $customerId
     */
    public function checkAlreadyExists($customerId)
    {
        $rowId = 0;
        $rewardRecordCollection = $this->_rewardRecordCollection->create()
            ->addFieldToFilter('customer_id', $customerId);
        if ($rewardRecordCollection->getSize()) {
            foreach ($rewardRecordCollection as $rewardRecord) {
                $rowId = $rewardRecord->getEntityId();
            }
        }
        return $rowId;
    }

    /**
     * Set Product Reward Data
     *
     * @param array $rewardProductData
     */
    public function setProductRewardData($rewardProductData)
    {
        $dataObjectProductDetail = $this->_rewardProductInterface->create();
        $this->_dataObjectHelper->populateWithArray(
            $dataObjectProductDetail,
            $rewardProductData,
            \Webkul\RewardSystem\Api\Data\RewardproductInterface::class
        );
        try {
            $this->_rewardProductRepository->save($dataObjectProductDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Set Category Reward Data
     *
     * @param array $rewardCategoryData
     */
    public function setCategoryRewardData($rewardCategoryData)
    {
        $dataObjectCategoryDetail = $this->_rewardCategoryInterface->create();
        $this->_dataObjectHelper->populateWithArray(
            $dataObjectCategoryDetail,
            $rewardCategoryData,
            \Webkul\RewardSystem\Api\Data\RewardcategoryInterface::class
        );
        try {
            $this->_rewardCategoryRepository->save($dataObjectCategoryDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Set Product Specific Reward Data
     *
     * @param array $rewardProductData
     */
    public function setProductSpecificRewardData($rewardProductData)
    {
        $dataObjectProductDetail = $this->_rewardproductSpecificInterface->create();
        $this->_dataObjectHelper->populateWithArray(
            $dataObjectProductDetail,
            $rewardProductData,
            \Webkul\RewardSystem\Api\Data\RewardproductSpecificInterface::class
        );
        try {
            $this->_rewardproductSpecificRepository->save($dataObjectProductDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Set Category Specific Reward Data
     *
     * @param array $rewardCategoryData
     */
    public function setCategorySpecificRewardData($rewardCategoryData)
    {
        $dataObjectCategoryDetail = $this->_rewardcategorySpecificInterface->create();
        $this->_dataObjectHelper->populateWithArray(
            $dataObjectCategoryDetail,
            $rewardCategoryData,
            \Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterface::class
        );
        try {
            $this->_rewardcategorySpecificRepository->save($dataObjectCategoryDetail);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Calculate Credit Amount for order for Priority Based
     *
     * @param int    $orderId
     * @param string $order
     */
    public function calculateCreditAmountforOrder($orderId = 0, $order = null)
    {
        $rewardPoint = 0;
        $priority = $this->getrewardPriority();
        if ($priority==0) {
            //product based
            $quantityWise = $this->getrewardQuantityWise();
            if ($order) {
                $cartData = $order->getAllVisibleItems();
            } elseif ($orderId!=0) {
                $order = $this->_orderModel->create()->load($orderId);
                $cartData = $order->getAllVisibleItems();
            } else {
                $cartData = $this->cartModel->getQuote()->getAllVisibleItems();
            }

            foreach ($cartData as $item) {
                if ($item['product_type'] == 'configurable' || $item['product_type'] == 'bundle') {
                    foreach ($item->getChildrenItems() as $singleItem) {
                        $rewardPoint += $this->getProductData($singleItem, $quantityWise);
                    }
                }
                $rewardPoint += $this->getProductData($item, $quantityWise);
            }
        } elseif ($priority==1) {
            //cart based
            $amount = $this->getGrandTotal($orderId);
            $rewardPoint = $this->getRewardBasedOnRules($amount);
        } elseif ($priority==2) {
          //category based
            if ($order) {
                $cartData = $order->getAllVisibleItems();
            } elseif ($orderId!=0) {
                $order = $this->_orderModel->create()->load($orderId);
                $cartData = $order->getAllVisibleItems();
            } else {
                $cartData = $this->cartModel->getQuote()->getAllVisibleItems();
            }
            foreach ($cartData as $item) {
                $rewardPoint += $this->getCategoryData($item);
            }
        } else {
            if ($order) {
                $cartData = $order->getAllVisibleItems();
            } elseif ($orderId!=0) {
                $order = $this->_orderModel->create()->load($orderId);
                $cartData = $order->getAllItems();
            } else {
                $cartData = $this->cartModel->getQuote()->getAllItems();
            }
            foreach ($cartData as $item) {
                $rewardPoint += $this->getAttributeData($item);
            }
        }
        if (!$rewardPoint) {
            return 0;
        }
        return $rewardPoint;
    }

    /**
     * Get Attribute Data
     *
     * @param object $item
     */
    public function getAttributeData($item)
    {
        $productId = $item->getProduct()->getId();
        $rewardpoint = 0;
        $product = $this->loadProduct($productId);
        $optionId = $product->getData($this->getAttributeCode());
        $attributeCode = $this->getAttributeCode();
        $collection = $this->_rewardattributeCollection->create()
                    ->addFieldToFilter('option_id', ['eq'=>$optionId])
                    ->addFieldToFilter('attribute_code', ['eq'=>$attributeCode])
                    ->addFieldToFilter('status', ['eq'=>1]);
        if ($collection->getSize()) {
            foreach ($collection as $attributeData) {
                $rewardpoint = $attributeData->getPoints();
            }
        }
        return $rewardpoint;
    }

    /**
     * Get Category Data
     *
     * @param object $item
     */
    public function getCategoryData($item)
    {
        $rewardpoint = $this->getCategorySpecificData($item);
        $categoryIds = $item->getProduct()->getCategoryIds();
        $categoryReward = [];
        if (is_array($categoryIds) && !$rewardpoint) {
            $categoryRewardCollection = $this->_rewardcategoryCollection
                                    ->create()
                                    ->addFieldToFilter('status', ['eq'=>1])
                                    ->addFieldToFilter('category_id', ['in'=>$categoryIds]);
            if ($categoryRewardCollection->getSize()) {
                foreach ($categoryRewardCollection as $categoryRule) {
                    $categoryReward[] = $categoryRule->getPoints();
                }
                if (!empty($categoryReward)) {
                    $rewardpoint = max($categoryReward);
                }
            }
        }
        return $rewardpoint;
    }

    /**
     * Get Category Specific Data
     *
     * @param object $item
     */
    public function getCategorySpecificData($item)
    {
        $categoryIds = $item->getProduct()->getCategoryIds();
        $rewardpoint = 0;
        $categoryReward = [];
        if (is_array($categoryIds)) {
            $categoryRewardCollection = $this->_rewardcategorySpecificCollection
                                  ->create()
                                  ->addFieldToFilter('status', ['eq'=>1])
                                  ->addFieldToFilter('category_id', ['in'=>$categoryIds]);
            if ($categoryRewardCollection->getSize()) {
                $cur_Time = $this->_date->gmtDate('H:i');
                $currentTime = $this->getTimeAccordingToTimeZone($cur_Time);
                foreach ($categoryRewardCollection as $categoryRule) {
                    $categoryStartTime = $this->getTimeAccordingToTimeZone($categoryRule->getStartTime());
                    $categoryEndTime = $this->getTimeAccordingToTimeZone($categoryRule->getEndTime());
                    if ((strtotime($currentTime) >= strtotime($categoryStartTime)) &&
                    (strtotime($currentTime) <= strtotime($categoryEndTime))) {
                        $categoryReward[] = $categoryRule->getPoints();
                    }
                }
                if (!empty($categoryReward)) {
                    $rewardpoint = max($categoryReward);
                }
            }
        }
        return $rewardpoint;
    }

    /**
     * Get SubTotal
     *
     * @param int $orderId
     */
    public function getSubTotal($orderId)
    {
        $subTotal = 0;
        $order = $this->_orderModel->create()->load($orderId);
        $subTotal = $order->getSubtotal();
        return $subTotal;
    }

    /**
     * Get Grand Total
     *
     * @param int $orderId
     */
    public function getGrandTotal($orderId)
    {
        $grandTotal = 0;
        $order = $this->_orderModel->create()->load($orderId);
        $grandTotal = $order->getGrandtotal();
        return $grandTotal;
    }

    /**
     * Get Reward Based On Rules
     *
     * @param int $amount
     */
    public function getRewardBasedOnRules($amount)
    {
        $today = $this->_date->gmtDate('Y-m-d');
        $reward = 0;
        $rewardCartruleCollection = $this->_rewardcartCollection
            ->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('start_date', ['lteq' => $today])
            ->addFieldToFilter('end_date', ['gteq' => $today])
            ->addFieldToFilter('amount_from', ['lteq'=>$amount])
            ->addFieldToFilter('amount_to', ['gteq'=>$amount]);
        if ($rewardCartruleCollection->getSize()) {
            foreach ($rewardCartruleCollection as $cartRule) {
                $reward = $cartRule->getPoints();
            }
        }
        return $reward;
    }

    /**
     * Get Product Data
     *
     * @param object $item
     * @param bool   $quantityWise
     */
    public function getProductData($item, $quantityWise)
    {
        $productId = $item->getProduct()->getId();
        $rewardpoint = 0;
        $qty = 0;
        $reward = $this->getProductReward($item->getProductId());
        if ($item->getOrderId() && $item->getOrderId()!=0) {
            $qty = $item->getQtyOrdered();
        } else {
            $qty = $item->getQty();
        }
        if ($quantityWise) {
            $rewardpoint = $reward * $qty;
        } else {
            $rewardpoint = $reward;
        }
        return $rewardpoint;
    }

    /**
     * Get Product Reward
     *
     * @param int $productId
     */
    public function getProductReward($productId)
    {
        $reward = $this->getProductSpecificData($productId);
        if (!$reward) {
            $productCollection = $this->_rewardProductCollection->create()
                               ->addFieldToFilter('product_id', ['eq'=>$productId])
                               ->addFieldToFilter('status', ['eq'=>1]);
            if ($productCollection->getSize()) {
                foreach ($productCollection as $productData) {
                    if ($productData->getPoints()) {
                        $reward = $productData->getPoints();
                    }
                }
            }
        }
        return $reward;
    }

    /**
     * Get Category Reward To Show
     *
     * @param int $categoryId
     */
    public function getCategoryRewardToShow($categoryId)
    {
        list($reward, $status, $message) = $this->getCategorySpecificDataToShow($categoryId);
        if (!$status) {
            $categoryCollection = $this->_rewardcategoryCollection->create()
                           ->addFieldToFilter('category_id', ['eq'=>$categoryId])
                           ->addFieldToFilter('status', ['eq'=>1]);
            if ($categoryCollection->getSize()) {
                foreach ($categoryCollection as $categoryData) {
                    if ($categoryData->getPoints()) {
                        $reward = $categoryData->getPoints();
                        $status = false;
                        $message = '';
                    }
                }
            }
        }
        return [$reward, $status, $message];
    }

    /**
     * Get Category Specific Data To Show
     *
     * @param int $categoryId
     */
    public function getCategorySpecificDataToShow($categoryId)
    {
        $reward = 0;
        $status = false;
        $message = '';
        $categoryCollection = $this->_rewardcategorySpecificCollection->create()
                           ->addFieldToFilter('category_id', ['eq'=>$categoryId])
                           ->addFieldToFilter('status', ['eq'=>1]);
        $curTime = $this->_date->gmtDate('H:i');
        $currentTime = $this->getTimeAccordingToTimeZone($curTime);
        if ($categoryCollection->getSize()) {
            foreach ($categoryCollection as $categoryData) {
                if ($categoryData->getPoints()) {
                    $startTime = $this->getTimeAccordingToTimeZone($categoryData->getStartTime());
                    $endTime = $this->getTimeAccordingToTimeZone($categoryData->getEndTime());
                    if ((strtotime($currentTime) >= strtotime($startTime)) &&
                     (strtotime($currentTime) <= strtotime($endTime))) {
                        $reward = $categoryData->getPoints();
                        $status = true;
                        $message = $this->_date->gmtDate(
                            'h:i A',
                            strtotime($startTime)
                        ).' - '.$this->_date->gmtDate('h:i A', strtotime($endTime));
                    }
                }
            }
        }
        return [$reward, $status, $message];
    }

    /**
     * Get Product Reward To Show
     *
     * @param int $productId
     */
    public function getProductRewardToShow($productId)
    {
        list($reward, $status, $message) = $this->getProductSpecificDataToShow($productId);
        if (!$status) {
            $productCollection = $this->_rewardProductCollection->create()
                             ->addFieldToFilter('product_id', ['eq'=>$productId])
                             ->addFieldToFilter('status', ['eq'=>1]);
            if ($productCollection->getSize()) {
                foreach ($productCollection as $productData) {
                    if ($productData->getPoints()) {
                        $reward = $productData->getPoints();
                        $status = false;
                        $message = '';
                    }
                }
            }
        }
        return [$reward, $status, $message];
    }

    /**
     * Get Product Specific Data To Show
     *
     * @param int $productId
     */
    public function getProductSpecificDataToShow($productId)
    {
        $reward = 0;
        $status = false;
        $message = '';
        $productCollection = $this->_rewardproductSpecificCollection->create()
                           ->addFieldToFilter('product_id', ['eq'=>$productId])
                           ->addFieldToFilter('status', ['eq'=>1]);
        $curTime = $this->_date->gmtDate('H:i');
        $currentTime = $this->getTimeAccordingToTimeZone($curTime);
        if ($productCollection->getSize()) {
            foreach ($productCollection as $productData) {
                if ($productData->getPoints()) {
                    $startTime = $this->getTimeAccordingToTimeZone($productData->getStartTime());
                    $endTime = $this->getTimeAccordingToTimeZone($productData->getEndTime());
                    if ((strtotime($currentTime) >= strtotime($startTime)) &&
                     (strtotime($currentTime) <= strtotime($endTime))) {
                        $reward = $productData->getPoints();
                        $status = true;
                        $message = $this->_date->gmtDate(
                            'h:i A',
                            strtotime($startTime)
                        ).' - '.$this->_date->gmtDate('h:i A', strtotime($endTime));
                    }
                }
            }
        }
        return [$reward, $status, $message];
    }

    /**
     * Get Product Specific Data
     *
     * @param int $productId
     */
    public function getProductSpecificData($productId)
    {
        $reward = 0;
        $productCollection = $this->_rewardproductSpecificCollection->create()
                           ->addFieldToFilter('product_id', ['eq'=>$productId])
                           ->addFieldToFilter('status', ['eq'=>1]);
        if ($productCollection->getSize()) {
            $cur_time = $this->_date->gmtDate('H:i');
            $currentTime = $this->getTimeAccordingToTimeZone($cur_time);
            foreach ($productCollection as $productData) {
                $startTime = $this->getTimeAccordingToTimeZone($productData->getStartTime());
                $endTime = $this->getTimeAccordingToTimeZone($productData->getEndTime());
                if ((strtotime($currentTime) >= strtotime($startTime)) &&
                 (strtotime($currentTime) <= strtotime($endTime)) &&
                $productData->getPoints()) {
                    $reward = $productData->getPoints();
                }
            }
        }
        return $reward;
    }

    /**
     * Get Attribute Options List
     *
     * @return array
     */
    public function getOptionsList()
    {
        $optionsList = ['' => 'Please Select'];
        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getAttributeCode());
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value']) {
                $optionsList[$option['value']] = $option['label'];
            }
        }
        return $optionsList;
    }

    /**
     * Get Attribute Options Values
     *
     * @return array
     */
    public function getOptionsValues()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getAttributeCode());
        $options = $attribute->getSource()->getAllOptions();
        return $options;
    }

    /**
     * Get Status List
     *
     * @return array
     */
    public function getStatusValues()
    {
        $statusList = [
            [
                'label' => __('Enabled'),
                'value' => 1
            ],
            [
                'label' => __('Disabled'),
                'value' => 0
            ]
        ];
        return $statusList;
    }

    /**
     * Get Status List
     *
     * @return array
     */
    public function getRewardPointStatusValues()
    {
        $statusList = [
            [
                'label' => __('Enable'),
                'value' => 1
            ],
            [
                'label' => __('Disable'),
                'value' => 0
            ]
        ];
        return $statusList;
    }

    /**
     * Get Attribute Code for Attribute Rule
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return  $this->scopeConfig->getValue(
            'rewardsystem/general_settings/attribute_reward',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reward points for the cart
     *
     * @return mixed
     */
    public function getCartReward()
    {
        $today = $this->_date->gmtDate('Y-m-d');
        $amountFrom = 0;
        $amonutTo = 0;
        $reward = 0;
        $cartAmount = $this->getCartTotal();
        $rewardCartruleCollection = $this->_rewardcartCollection
          ->create()
          ->addFieldToFilter('status', 1)
          ->addFieldToFilter('start_date', ['lteq' => $today])
          ->addFieldToFilter('end_date', ['gteq' => $today])
          ->addFieldToFilter('amount_from', ['lteq' => $cartAmount])
          ->addFieldToFilter('amount_to', ['gteq' => $cartAmount]);
        if ($rewardCartruleCollection->getSize()) {
            foreach ($rewardCartruleCollection as $cartRule) {
                $reward = $cartRule->getPoints();
                $amountFrom = $cartRule->getAmountFrom();
                $amonutTo = $cartRule->getAmountTo();
            }
        }
        return [
        'reward' => $reward,
        'amount_from' => $amountFrom,
        'amount_to' => $amonutTo
        ];
    }

    /**
     * Get Cart Data cart Quantity for show Message on Cart Page
     *
     * @return int
     */
    public function getCartData()
    {
        return $this->cartModel->getQuote()->getItemsCount();
    }

    /**
     * Get Cart All Data cart Complete Data for show Message on Cart Page
     *
     * @return array
     */
    public function getCartAllData()
    {
        return $this->cartModel->getQuote()->getItemsCollection();
    }

    /**
     * Get Cart Total Cart Grand Total For Show Reward Ponit on Cart Page
     *
     * @return float
     */
    public function getCartTotal()
    {
        return $this->cartModel->getQuote()->getGrandTotal();
    }

    /**
     * Get Order Url by Order Id
     *
     * @param  integer $orderId
     * @return string Order view Url
     */
    public function getOrderUrl($orderId = 0)
    {
        return $this->_getUrl(
            'sales/order/view',
            ['order_id'=> $orderId]
        );
    }

    /**
     * Clean Cache
     */
    public function clearCache()
    {
        $cacheManager = $this->cacheManager->create();
        $availableTypes = $cacheManager->getAvailableTypes();
        $cacheManager->clean($availableTypes);
    }

    /**
     * Prepare transaction note on priority wise
     *
     * @param int $incrementId
     */
    public function getTransactionNotePriorityWise($incrementId)
    {
        $priority = $this->getrewardPriority();
        $transactionNote = __('Order id : %1 credited amount', $incrementId);
        if ($priority == 3) {
            $transactionNote = __('Order id : %1 credited amount in way', $incrementId);
        }
        return $transactionNote;
    }

    /**
     * Load Customer Data
     *
     * @param string $customerId
     *
     * @return object
     */
    public function loadCustomer($customerId = '')
    {
        if ($customerId == '') {
            $customerId = $this->_customerSession->getCustomer()->getId();
        }
        $customer = $this->_customerModel->create()->load($customerId);
        return $customer;
    }

    /**
     * Load Product Data
     *
     * @param string $productId
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProduct($productId)
    {
        $product = $this->productModel->create()->load($productId);
        return $product;
    }

    /**
     * Get Product Reward Info
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getProductRewardInfo($product)
    {
        $productPrice = $product->getFinalPrice();
        $rewardValue = $this->getRewardValue();
        $pointsRequired = $productPrice/$rewardValue;
        $productId = $product->getId();
        list($productRewardPoints, $status, $message) = $this->getProductRewardToShow($productId);

        $minPrice = $maxPrice = $sumOfRewardPoints = $minRewardPoint = $maxRewardPoint = 0;

        if ($product->getTypeId() == 'bundle') {
            $minimalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            $maximalprice = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();

            $bundledProduct = $product->getTypeInstance(true)->getOptions($product);
            $bundledProductIds = $product->getTypeInstance(true)->getChildrenIds($product->getId(), true);
            
            list($parentRewardPoint, $parentStatus, $parentMessage) =  $this->getProductRewardToShow($product->getId());
            $minPrice = $minimalPrice;
            $maxPrice = $maximalprice;
            $maxRewardPoint = 0;
            
            $minRewardPoint = 0;
            $sumOfRewardPoints = 0;
            $message = '';
            $status = false;
            if ($parentMessage != null) {
                $message = $parentMessage;
            }
            if ($parentStatus == true) {
                $status = true;
            }

            foreach ($bundledProduct as $child) {
                $bundleChildPro = $bundledProductIds[$child['option_id']];
                $childType = $child['type'];
                $childMaxRPForRadio = 0;
                $childMaxRPForSelect = 0;
                $childMaxRPForMulti = 0;
                $childMaxRPForCheckBox = 0;

                $childMinRPForRadio = 0;
                $childMinRPForSelect = 0;
                $childMinRPForMulti = 0;
                $childMinRPForCheckBox = 0;
                
                foreach ($bundleChildPro as $subchild) {
                    list($rewardPoint, $childStatus, $childMessage) =  $this->getProductRewardToShow($subchild);
                    if ($childMessage != null) {
                        $message = $childMessage;
                    }
                    if ($childStatus == true) {
                        $status = true;
                    }
                    if ($childType == 'radio') {
                        $childMinRPForRadio = 9999999;
                        if ($rewardPoint > $childMaxRPForRadio) {
                            $childMaxRPForRadio = $rewardPoint;
                        }
                        if ($rewardPoint < $childMinRPForRadio) {
                            $childMinRPForRadio = $rewardPoint;
                        }
                    }
                    if ($childType == 'select') {
                        $childMinRPForSelect = 99999999;
                        if ($rewardPoint > $childMaxRPForSelect) {
                            $childMaxRPForSelect = $rewardPoint;
                        }
                        if ($rewardPoint < $childMinRPForSelect) {
                            $childMinRPForSelect = $rewardPoint;
                        }
                    }
                    if ($childType == 'checkbox') {
                        $childMinRPForCheckBox = 9999999999999;
                            $childMaxRPForCheckBox += $rewardPoint;
                        if ($rewardPoint < $childMinRPForCheckBox) {
                            $childMinRPForCheckBox = $rewardPoint;
                        }
                    }
                    if ($childType == 'multi') {
                        $childMinRPForMulti = 99999999999;
                            $childMaxRPForMulti += $rewardPoint;
                        if ($rewardPoint < $childMinRPForMulti) {
                            $childMinRPForMulti = $rewardPoint;
                        }
                    }
                }
                $childMaxRewardPoints = $childMaxRPForRadio + $childMaxRPForSelect + $childMaxRPForMulti +
                $childMaxRPForCheckBox;
                $childMinRewardPoints = $childMinRPForRadio + $childMinRPForSelect + $childMinRPForMulti +
                $childMinRPForCheckBox;

                $maxRewardPoint += $childMaxRewardPoints;
                $minRewardPoint += $childMinRewardPoints;
            }
            $maxRewardPoint += $parentRewardPoint;
            $minRewardPoint += $parentRewardPoint;
            $pointsRequired = round($maxRewardPoint, 0);
            $productRewardPoints = round($maxRewardPoint, 0);
        }

        if ($product->getTypeId() == 'grouped') {
            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $maxPrice = 0;
            $minPrice = 999999999;
            $minRewardPoint = 99999999999;
            $maxRewardPoint = 0;
            $message = '';
            $status = false;
            
            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                    list($rewardPoint, $childStatus, $childMessage) =  $this->getProductRewardToShow($child->getId());
                    if ($childMessage != null) {
                        $message = $childMessage;
                    }
                    if ($childStatus == true) {
                        $status = true;
                    }
                    $maxPrice += $child->getFinalPrice();

                    if ($child->getFinalPrice() < $minPrice) {
                        $minPrice = $child->getFinalPrice();
                    }
                    if ($rewardPoint < $minRewardPoint) {
                        $minRewardPoint = $rewardPoint;
                    }
                    $maxRewardPoint += $rewardPoint;
                }
            }
            if ($minRewardPoint == $maxRewardPoint) {
                $minRewardPoint = 0;
            }
                $minRewardPoint = round($minRewardPoint, 0);
                $maxRewardPoint = round($maxRewardPoint, 0);
                $pointsRequired = $maxRewardPoint;
                $productRewardPoints = $maxRewardPoint;
                $minPrice = $minPrice/$rewardValue;
                $maxPrice = $maxPrice/$rewardValue;
        }

        if ($product->getTypeId() == 'configurable') {
            $usedProds = $product->getTypeInstance(true)->getUsedProducts($product);
            list($parentRewardPoint, $parentStatus, $parentMessage) =  $this->getProductRewardToShow($product->getId());
            $maxPrice = 0;
            $minPrice = 999999999;
            $maxRewardPoint = 0;
            
            $minRewardPoint = 999999999;
            $sumOfRewardPoints = 0;
            $message = '';
            $status = false;
            if ($parentMessage != null) {
                $message = $parentMessage;
            }
            if ($parentStatus == true) {
                $status = true;
            }

            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                    list($rewardPoint, $childStatus, $childMessage) =  $this->getProductRewardToShow($child->getId());
                    $sumOfRewardPoints += $rewardPoint;
                    if ($childMessage != null) {
                        $message = $childMessage;
                    }
                    if ($childStatus == true) {
                        $status = true;
                    }
                    if ($maxPrice < $child->getFinalPrice()) {
                        $maxPrice = $child->getFinalPrice();
                    }
                    if ($child->getFinalPrice() < $minPrice) {
                        $minPrice = $child->getFinalPrice();
                    }
                    if ($rewardPoint < $minRewardPoint) {
                        $minRewardPoint = $rewardPoint;
                    }
                    if ($rewardPoint > $maxRewardPoint) {
                        $maxRewardPoint = $rewardPoint;
                    }
                }
            }
                $minRewardPoint = round(($minRewardPoint + $parentRewardPoint), 0);
                $maxRewardPoint = round(($maxRewardPoint + $parentRewardPoint), 0);
                $minPrice = $minPrice/$rewardValue;
                $maxPrice = $maxPrice/$rewardValue;
                $pointsRequired = $maxPrice;
                $productRewardPoints = $maxRewardPoint;
            if ($sumOfRewardPoints == 0) {
                $productRewardPoints = round($parentRewardPoint, 0);
            }
        }

        return [
            $productRewardPoints,
            $minPrice,
            $maxPrice,
            $pointsRequired,
            $sumOfRewardPoints,
            $minRewardPoint,
            $maxRewardPoint,
            $status,
            $message
        ];
    }

    /**
     * Function get Json Serializer
     *
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getJsonSerializer()
    {
        return $this->json;
    }

    /**
     * Function get reward info from quote
     *
     * @param object $quote
     * @return array|string
     */
    public function getRewardInfoFromQuote($quote)
    {
        if ($quote->getRewardInfo()) {
            return $this->json->unserialize($quote->getRewardInfo());
        }
        return '';
    }

    /**
     * Function set reward info in quote
     *
     * @param object $quote
     * @param array|string $rewardInfo
     * @return void
     */
    public function setRewardInfoInQuote($quote, $rewardInfo)
    {
        if (is_array($rewardInfo)) {
            $rewardInfo = $this->json->serialize($rewardInfo);
        }
        $quote->setRewardInfo($rewardInfo)->save();
    }

    /**
     * Function unset reward info in quote
     *
     * @param object $quote
     * @return void
     */
    public function unsetRewardInfoInQuote($quote)
    {
        $this->setRewardInfoInQuote($quote, '');
    }

    /**
     * Function processReferralMail
     *
     * @param array $data
     * @param int $storeId
     */
    public function processReferralInvitation($data, $storeId = 0)
    {
        $emailString = $data['email'];
        $referralUrl = $data['referral_link'];

        $adminEmail= $this->getDefaultTransEmailId();
        $adminUsername = $this->getDefaultTransName();
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];

        $emailArray = explode(",", $emailString);
        foreach ($emailArray as $email) {
            $email = trim($email);
            $receiverInfo = [
                'name' => $email,
                'email' => $email,
            ];

            try {
                $this->_mailHelper->sendReferralInvitationMail($receiverInfo, $senderInfo, $referralUrl, $storeId);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Unable to send invitation mail to %1.', $email)
                );
            }
        }
    }

    /**
     * Check if referral registration or not.
     *
     * @return bool
     */
    public function getReferralRegistrationCode()
    {
        $code = $this->request->getParam('referral');
        if ($code) {
            return $code;
        }
        return false;
    }

    /**
     * Function processReferralOnRegistration
     *
     * @param int $referralCode
     * @param int $refereeId
     * @return void
     */
    public function processReferralOnRegistration($referralCode, $refereeId)
    {
        $referrerId = $referralCode;
        $customerRewardOnReferral = $this->getCustomerRewardOnReferral() ?: 0;
        $refereeRewardOnReferral = $this->getRefereeRewardOnReferral() ?: 0;
        $referralDetail = $this->referralDetailFactory->create();
        $referralData = [
            'customer_id' => $referrerId,
            'referee_id' => $refereeId,
            'customer_reward_point' => $customerRewardOnReferral,
            'referee_reward_point' => $refereeRewardOnReferral,
            'status' => 1,
            'created_at' => $this->_date->gmtDate('d-m-Y')
        ];
        $referralDetail->addData($referralData)->save();
        if ($customerRewardOnReferral) {
            $transactionNote = __("Reward point for referral");
            $rewardData = [
                'customer_id' => $referrerId,
                'points' => $customerRewardOnReferral,
                'type' => 'credit',
                'review_id' => 0,
                'order_id' => 0,
                'status' => 1,
                'note' => $transactionNote
            ];
            $msg = __(
                'You got %1 reward points through referral program',
                $customerRewardOnReferral
            )->render();
            $adminMsg = __(
                ' got %1 reward points through referral program',
                $customerRewardOnReferral
            )->render();
            $this->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
        }
        if ($refereeRewardOnReferral) {
            $transactionNote = __("Reward point on registration through referral");
            $rewardData = [
                'customer_id' => $refereeId,
                'points' => $refereeRewardOnReferral,
                'type' => 'credit',
                'review_id' => 0,
                'order_id' => 0,
                'status' => 1,
                'note' => $transactionNote
            ];
            $msg = __(
                'You got %1 reward points on registration through referral program',
                $refereeRewardOnReferral
            )->render();
            $adminMsg = __(
                ' have registered on your site, and got %1 reward points through referral program',
                $refereeRewardOnReferral
            )->render();
            $this->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
            $this->messageManager->addSuccess(__(
                'You got %1 reward points on registration through referral program',
                $refereeRewardOnReferral
            ));
        }
    }
}
