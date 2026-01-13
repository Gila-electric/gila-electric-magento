<?php
/**
 * Webkul DailyDeals CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class LoadQuoteBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Downloadable\Api\LinkRepositoryInterface
     */
    private $linkRepositoryInterface;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    private $linkFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepositoryInterface
     * @param \Webkul\DailyDeals\Helper\Data $helperData
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepositoryInterface,
        \Webkul\DailyDeals\Helper\Data $helperData,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeInterface;
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->linkRepositoryInterface  = $linkRepositoryInterface;
        $this->linkFactory = $linkFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getRequest()->getParam('product');
        $storeId = $this->storeManager->getStore()->getId();
        $item = $this->getProduct($productId, $storeId);
        if ($item) {
            $itemId = $item->getId();
            $routName = $this->request->getRouteName();
            $actionName = $this->request->getFullActionName();
            $params = $this->request->getParams();
            $updated = false;
            $modEnable = true;
            if ($modEnable && $actionName!= "checkout_sidebar_removeItem") {
                if ($routName != 'adminhtml' && strpos($actionName, 'mpquotesystem') === false) {
                    $amount = 0;
                    $linkPrice = 0;
                    //Get Link Product Price (Downloadable product links)
                    if (isset($params['links'])) {
                        $linkPrice = $this->getDownloadableLinkPrice($params);
                    }
                    $productId = $item->getProductId();
                    //for configurable product
                    if (isset($params['selected_configurable_option'])
                    && !empty($params['selected_configurable_option'])) {
                        $productId = $params['selected_configurable_option'];
                    }
                    $product = $this->productRepository->getById($productId);
                            
                    $dealStatus = $this->helperData->getProductDealDetail($product);
                    $proDealStatus = $product->getDealStatus();
                    
                    $customOptionPrice = $this->getCustomOptionPrice($product);
                    $price = $product->getPrice();
                    
                    if ($dealStatus === false && $proDealStatus) {
                        $price = $product->getPrice() + $customOptionPrice + $linkPrice;
                        $item->setPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->setCustomPrice($price);
                        $item->getProduct()->setIsSuperMode(true);
                        $updated = true;
                    } elseif ($proDealStatus) {
                        $price = $product->getSpecialPrice() + $customOptionPrice + $linkPrice;
                        $item->setPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->setCustomPrice($price);
                        $item->getProduct()->setIsSuperMode(true);
                        $updated = true;
                    }
                    $amount +=$price;
                    $item->save();
                    
                    if ($updated) {
                        $this->_checkoutSession->getQuote()->setItemsQty(1);
                        $this->_checkoutSession->getQuote()->setSubtotal($amount);
                        $this->_checkoutSession->getQuote()->setGrandTotal($amount);
                        $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get product
     *
     * @param int $id
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product|false
     */
    public function getProduct($id, $storeId)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId, false, $storeId);
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $this->_checkoutSession->getQuote()->getItemByProduct($product);
            return $item;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Downloadable Link Price
     *
     * @param array $params
     * @return float
     */
    public function getDownloadableLinkPrice($params)
    {
        $price = 0;
        $linFactoryCollection = $this->linkFactory->create()->getCollection();
        
        $dlPrice = $linFactoryCollection->getResource()->getTable('downloadable_link_price');
        $linFactoryCollection->getSelect()->join(
            $dlPrice.' as dlp',
            'main_table.link_id = dlp.link_id',
        );
        $linFactoryCollection->addFieldToFilter('main_table.link_id', ['in'=>$params['links']]);
        foreach ($linFactoryCollection as $collection) {
            
            $price +=$collection->getPrice();
        }
        
        return $price;
    }
    
    /**
     * Get custom option price
     *
     * @param collection $product
     * @return void
     */
    public function getCustomOptionPrice($product)
    {
        $finalPrice = 0;
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), 0);
                }
            }
        }
        return $finalPrice;
    }
}
