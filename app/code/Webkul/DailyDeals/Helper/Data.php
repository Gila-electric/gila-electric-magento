<?php
namespace Webkul\DailyDeals\Helper;

/**
 * Webkul_DailyDeals data helper
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProTypeModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    public $fullPageCache;
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LinkRepositoryInterface
     */
    private $linkRepositoryInterface;
    
    /**
     * @var GroupedProTypeModel
     */
    public $_groupedProTypeModel;
    /**
     * @var ConfigurableProTypeModel
     */
    public $_configurableProTypeModel;

    /**
     * @var ProductCollectionFactory
     */
    public $_productCollection;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    public $response;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    public $cacheTypeList;

    /**
     * @var ProductOptionRepositoryInterface
     */
    public $productOption;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;
    
    /**
     * @var CartRepositoryInterface
     */
    public $quoteRepository;

    /**
     * @var currencyFactory
     */
    public $currencyFactory;

    /**
     * @var  MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteIdInterface;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param TimezoneInterface $localeDate
     * @param PricingHelper $pricingHelper
     * @param LinkRepositoryInterface $linkRepositoryInterface
     * @param ConfigurableProTypeModel $configurableProTypeModel
     * @param GroupedProTypeModel $groupedProTypeModel
     * @param ProductOptionRepositoryInterface $productOption
     * @param ProductCollectionFactory $productCollection
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteIdInterface
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $localeDate,
        PricingHelper $pricingHelper,
        LinkRepositoryInterface $linkRepositoryInterface,
        ConfigurableProTypeModel $configurableProTypeModel,
        GroupedProTypeModel $groupedProTypeModel,
        ProductOptionRepositoryInterface $productOption,
        ProductCollectionFactory $productCollection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteIdInterface,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->response = $response;
        $this->localeDate = $localeDate;
        $this->cacheTypeList = $cacheTypeList;
        $this->pricingHelper = $pricingHelper;
        $this->productRepository = $productRepository;
        $this->linkRepositoryInterface = $linkRepositoryInterface;
        $this->productOption = $productOption;
        $this->_groupedProTypeModel = $groupedProTypeModel;
        $this->_configurableProTypeModel = $configurableProTypeModel;
        $this->_productCollection = $productCollection;
        $this->cart = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->currencyFactory = $currencyFactory;
        $this->json = $json;
        parent::__construct($context);
    }
    /**
     * Get product price
     *
     * @param int $productId
     * @return float
     */
    public function getProductPrice($productId)
    {
        $product = $this->productRepository->getById($productId);
        $product->setStoreId(0);
        $modEnable = $this->isModEnable();
        $price = 0;
        if ($modEnable && is_numeric($product->getDealValue())) {
            $dealToDate = $product->getDealToDate();
            $isdailyDealTimeOver = $this->isDailyDealTimeOver($dealToDate);
            if ($isdailyDealTimeOver) {
                $curentCurrencyCode =  $this->getCurrentCurrency();
                $baseCurrencyCode =  $this->getBaseCurrency();
                $price = $product->getPrice();
                if ($curentCurrencyCode!=$baseCurrencyCode) {
                    $price = $this->convertBaseCurrencyToCurrentCurrency($price, $baseCurrencyCode);
                }
                
            }
        }
        
        return $price;
    }
    /**
     * Get product deal details
     *
     * @param object $product
     * @return void
     */
    public function getProductDealDetail($product)
    {
       
        $product = $this->productRepository->getById($product->getEntityId());
        $product->setStoreId(0);
        $dealStatus = $product->getDealStatus();
        $content = false;
        $modEnable = $this->isModEnable();
        if ($dealStatus && $modEnable && is_numeric($product->getDealValue())) {
            $content = ['deal_status' => $dealStatus];
            $today = $this->getCurrentDateTime();
            $dealFromDateTime = $this->localeFormatDateTime($product->getDealFromDate());
            $dealToDateTime = $this->localeFormatDateTime($product->getDealToDate());
            $difference = strtotime($dealToDateTime) - strtotime($today);
            $specialPrice = $product->getSpecialPrice();
            $price = $product->getPrice();
            if ($modEnable && $difference > 0 && $dealFromDateTime < $today) {
                $content['update_url'] = $this->_urlBuilder->getUrl('dailydeals/index/updatedealinfo');
                $content['stoptime'] = $product->getSpecialToDate();
                $content['diff_timestamp'] = $difference;
                $content['discount-percent'] = $product->getDealDiscountPercentage();
                $content['special-price'] = $specialPrice;
                if ($product->getTypeId() != 'bundle') {
                    $content['saved-amount'] = $this->pricingHelper
                            ->currency($price - $specialPrice, true, false);
                }
                if ($product->getTypeId() != 'configurable') {
                    $this->setPriceAsDeal($product);
                }
            } elseif ($modEnable && $dealToDateTime <= $today) {
                $this->updateMiniCartItems();
                
                $token = 0;
                
                if ($product->getDealStatus()) {
                    $token = 1;
                }
                if ($token) {
                    $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->setDealStatus(0);
                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    $product->save();
                    $content = false;
                    $this->cacheFlush();
                }
                $content = false;
            }
        }
        return $content;
    }
    /**
     * Get Locale Format Date Time
     *
     * @param DateTime $dateTime
     * @param string $format
     * @return DateTime
     */
    public function localeFormatDateTime($dateTime, $format = 'Y-m-d H:i:s')
    {
        return $this->localeDate->date(
            strtotime($dateTime)
        )->format($format);
    }

    /**
     * Update Product deal Detail
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $value
     * @return void
     */
    public function updateProductDealDetail($product, $value)
    {
        $modEnable = $this->isModEnable();
        $dealStatus = $value['deal_status'];
        if ($dealStatus && $modEnable && is_numeric($value['deal_value'])) {
            $currentDateTime = $this->getCurrentDateTime();
            $dealFromDateTime = $this->localeFormatDateTime($product->getDealFromDate());
            $dealToDateTime = $this->localeFormatDateTime($product->getDealToDate());
            $difference = strtotime($dealToDateTime) - strtotime($currentDateTime);
            if ($difference > 0 && $dealFromDateTime < $currentDateTime) {
                if ($product->getTypeId() != 'configurable') {
                    $this->updateSpecialPriceData($product, $value);
                }
            } elseif ($dealToDateTime <= $currentDateTime) {
                if ($product->getDealStatus()) {
                    $token = 1;
                }
                if ($token) {
                    $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->setDealStatus(0);
                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    $product->save();
                }
            }
        }
    }

    /**
     * Get Deal value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getDealValue($product)
    {

        $dealvalue = $product->getDealValue();
        $proType = $product->getTypeId();
        if ($product->getDealDiscountType() == 'percent') {
            if ($proType != 'bundle') {
                $price = $product->getPrice() * ($dealvalue/100);
            } else {
                $price = $dealvalue;
            }
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        return [$price,$discount];
    }

    /**
     * Update Special Price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $value
     * @return void
     */
    public function updateSpecialPriceData($product, $value)
    {
        $product = $this->productRepository->getById($product->getEntityId());
        $product->setStoreId(0);
        $proType = $product->getTypeId();
        list($price,$discount) = $this->getDealValue($product);
        $token = 0;
        if (is_numeric($product->getSpecialPrice())) {
            $token = 1;
        }
        $product->setSpecialFromDate(date("m/d/Y", strtotime($value['deal_from_date'])));
        $product->setSpecialToDate(date("m/d/Y", strtotime($value['deal_to_date'])));
        $product->setSpecialPrice($price);
        $product->setDealDiscountPercentage(round(100-$discount));

        if ($proType != 'bundle') {
            if (!$token) {
                $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $product->save();
                $product->unsetDealStatus();
            }
        } else {
            $optionCollection = $this->productOption->getList($product->getSku());
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
            $selectionData = $selectionCollection->getData();
            foreach ($optionCollection as $option) {
                if ($option->getRequired() && $option->getSelections() == 1) {
                    $selectionData [] = $option->getSelections();
                    $selections = $selectionData;
                } else {
                    $selections = [];
                    break;
                }
            }
            $extension = $product->getExtensionAttributes();
            $extension->setBundleProductOptions($selections);
            $product->setExtensionAttributes($extension);
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
            $product->save();
        }
        $product->save();
    }

    /**
     * Update mini cart items
     *
     * @return void
     */
    public function updateMiniCartItems()
    {
        $items = $this->cart->getQuote()->getAllItems();
       
        foreach ($items as $item) {
            $productId = $item->getProduct()->getId();
            $price = $this->getProductPrice($productId);
            if (!empty($price)) {
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
        $this->cart->getQuote()->collectTotals();
        $this->quoteRepository->save($this->cart->getQuote());
    }

    /**
     * SetPriceAsDeal
     *
     * @param ProductRepositoryInterface $product
     * @return void
     */
    public function setPriceAsDeal($product)
    {
        $tempproduct = $this->productRepository->getById($product->getEntityId());
        $tempproduct->setStoreId($this->storeManager->getStore()->getId());
        $proUrl = $tempproduct->getProductUrl();
        $product->setStoreId(0);
        $proType = $product->getTypeId();
        $dealvalue = $product->getDealValue();
        if ($product->getDealDiscountType() == 'percent') {
            if ($proType != 'bundle') {
                $price = $product->getPrice() * ($dealvalue/100);
            } else {
                $price = $dealvalue;
            }
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        $token = 0;
        if (is_numeric($product->getSpecialPrice())) {
            $token = 1;
        }
        $product->setSpecialFromDate(date("m/d/Y", strtotime($product->getDealFromDate())));
        $product->setSpecialToDate(date("m/d/Y", strtotime($product->getDealToDate())));
        $product->setSpecialPrice($price);
        $product->setDealDiscountPercentage(round(100-$discount));
        if ($proType != 'bundle') {
            if (!$token) {
                $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $product->save();
                $config = $this->_configurableProTypeModel
                                    ->getParentIdsByChild($product->getEntityId());
               
                if ($this->request->getFullActionName() == 'catalog_product_view'
                        && !isset($config[0])) {
                    if (strpos($proUrl, '?___store=admin')===false) {
                        $this->cacheFlush();
                        $this->response->setRedirect($proUrl);
                    }
                }
                $product->unsetDealStatus();
            }
        } else {
            $optionCollection = $this->productOption->getList($product->getSku());
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
            $selectionData = $selectionCollection->getData();
            foreach ($optionCollection as $option) {
                if ($option->getRequired() && $option->getSelections() == 1) {
                    $selectionData [] = $option->getSelections();
                    $selections = $selectionData;
                } else {
                    $selections = [];
                    break;
                }
            }
            $extension = $product->getExtensionAttributes();
            $extension->setBundleProductOptions($selections);
            $product->setExtensionAttributes($extension);
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
            $product->save();
        }
    }
    /**
     * Get Deal Product ids
     *
     * @return void
     */
    public function getDealProductIds()
    {
        $ids = [];
        $modEnable = $this->isModEnable();
        if ($modEnable) {
            $collection = $this->_productCollection->create();
            $collection->addAttributeToFilter('deal_status', 1);
            $collection->addAttributeToFilter('deal_from_date', ['lt'=>$this->getCurrentDateTime()]);
            $collection->addAttributeToFilter('deal_to_date', ['gt'=>$this->getCurrentDateTime()]);

            $ids = $collection->getColumnValues('entity_id');
            $ids = $this->getConfigurableProIds($ids, $collection);
            $ids = $this->getGroupedProIds($ids, $collection);
        }
        return $ids;
    }

    /**
     * Get configurable Pro ids
     *
     * @param int $ids
     * @param collection $collection
     * @return void
     */
    public function getConfigurableProIds($ids, $collection)
    {
        $associatedProdIds =  $collection->addAttributeToFilter('visibility', ['eq' => 1])
                                ->getColumnValues('entity_id');
        foreach ($associatedProdIds as $id) {
            $details = $this->_configurableProTypeModel->getParentIdsByChild($id);
            if (isset($details[0])) {
                array_push($ids, $details[0]);
            }
        }
        return $ids;
    }
    /**
     * Get grouped pro ids
     *
     * @param int $ids
     * @param collection $collection
     * @return void
     */
    public function getGroupedProIds($ids, $collection)
    {
        $associatedProdIds =  $collection->addAttributeToFilter('visibility', ['eq' => 1])
                                ->getColumnValues('entity_id');
        foreach ($associatedProdIds as $id) {
            $details = $this->_groupedProTypeModel->getParentIdsByChild($id);
            if (isset($details[0])) {
                array_push($ids, $details[0]);
            }
        }
        return $ids;
    }

    /**
     * Get max discount
     *
     * @param array $allDeals
     * @return void
     */
    public function getMaxDiscount($allDeals)
    {
        $minVal = 99999999;
        $result = [];
        foreach ($allDeals as $deal) {
            if ($deal['special-price']<=$minVal) {
                $minVal = $deal['special-price'];
                $result = $deal;
            }
        }
        return $result;
    }
    /**
     * Convert Price From Base Currency to Currenct currency
     *
     * @param float $price
     * @param string $baseCurrencyCode
     * @return float
     */
    public function convertBaseCurrencyToCurrentCurrency(
        $price,
        $baseCurrencyCode
    ) {
        $curentCurrencyCode =  $this->getCurrentCurrency();
        if ($baseCurrencyCode != $curentCurrencyCode) {
            $rate = $this->currencyFactory->create()
                ->load($baseCurrencyCode)
                ->getAnyRate($curentCurrencyCode);
            $price = round($price * $rate);
        }
        return $price;
    }
    /**
     * Flush Cache
     */
    public function cacheFlush()
    {
        $types = ['full_page', 'block_html'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
    }
    /**
     * Get Cache;
     *
     * @return \Magento\PageCache\Model\Cache\Type
     */
    protected function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\PageCache\Model\Cache\Type::class
            );
        }
        return $this->fullPageCache;
    }

    /**
     *  Clean By Tags
     *
     * @param int $productId
     * @return void
     */
    public function cleanByTags($productId)
    {
        $tags = ['CAT_P_'.$productId];
        $this->getCache()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
    }

    /**
     * Get Quote Currency Code
     *
     * @return void
     */
    public function getQuoteCurrencyCode()
    {
        $quote = $this->cart->getQuote();
        return $quote->getQuoteCurrencyCode();
    }

    /**
     * Update items on currency change
     *
     * @return void
     */
    public function updateItemsOnCurrencyChange()
    {
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $item) {
            $price = $this->getSwitchProductPrice($item);
            if (!empty($price)) {
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
         $this->cart->getQuote()->collectTotals();
         $this->quoteRepository->save($this->cart->getQuote());
    }

    /**
     * Get Switch Producti
     *
     * @param object $item
     * @return void
     */
    public function getSwitchProductPrice($item)
    {
        $product = $this->productRepository->getById($item->getProduct()->getId());
        $product->setStoreId(0);
        $modEnable = $this->isModEnable();
        $price = 0;
        if ($modEnable && is_numeric($product->getDealValue())) {
            $dealToDate = $product->getDealToDate();
            $isdailyDealTimeOver = $this->isDailyDealTimeOver($dealToDate);
            if ($isdailyDealTimeOver) {
                $curentCurrencyCode =  $this->getCurrentCurrency();
                $baseCurrencyCode =  $this->getBaseCurrency();
                $getQuoteCurrencyCode = $this->getQuoteCurrencyCode();
                $price = $item->getProduct()->getPrice();
                if ($curentCurrencyCode != $baseCurrencyCode) {
                    $price =  $this->convertBaseCurrencyToCurrentCurrency(
                        $item->getCustomPrice(),
                        $getQuoteCurrencyCode
                    );
                }
            }
        }
        return $price;
    }

    /**
     * Is module enable
     *
     * @return boolean
     */
    public function isModEnable()
    {
        return true;
    }

    /**
     * Is Daily deal time over
     *
     * @param date $dealToDate
     * @return boolean
     */
    public function isDailyDealTimeOver($dealToDate)
    {
        $today = $this->getCurrentDateTime();
        $flag = false;
        $dealToDateTime = $this->localeFormatDateTime($dealToDate);
        $difference = strtotime($dealToDateTime) - strtotime($today);
        if ($difference <= 0) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * Get Current Currency
     *
     * @return string
     */
    public function getCurrentCurrency()
    {
        return $this->storeManager->getStore()
        ->getCurrentCurrency()
        ->getCode();
    }

    /**
     * Get Base Currency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return  $this->storeManager->getStore()
        ->getBaseCurrency()
        ->getCode();
    }
    
    /**
     * Get Current DateTime
     *
     * @return void
     */
    public function getCurrentDateTime()
    {
        return $this->localeDate->date()->format('Y-m-d H:i:s');
    }

    /**
     * Check authentication
     *
     * @param Context $context
     * @param boolean $isSellerRequest
     * @return throw GraphQlAuthorizationException
     */
    public function checkAuthentication($context, $isSellerRequest = true)
    {
        /** @var ContextInterface $context */
        $isCustomer = $context->getExtensionAttributes()->getIsCustomer();
        if (false === $isCustomer) {
            throw new GraphQlAuthorizationException(__('The current customer is n\'t authorized.'));
        }
        if ($isSellerRequest) {
            $isSeller = $this->isSeller($context->getUserId());
            if (!$isSeller) {
                throw new GraphQlAuthorizationException(__('To Become Seller Please Contact to Admin.'));
            }
        }
    }

    /**
     * This function will return json encoded data
     *
     * @param  array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->json->serialize($data);
    }

    /**
     * This function will return json decode data
     *
     * @param  string $data
     * @return array
     */
    public function jsonDecode($data)
    {
        return $this->json->unserialize($data);
    }
}
