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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param TimezoneInterface $localeDate
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ScopeConfigInterface $scopeInterface
    ) {
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->scopeConfig = $scopeInterface;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productData = $this->request->getParam('product');
        $modEnable = true;
        if ($product->getDealStatus() && $modEnable) {
            if (empty($productData)) {
                return $this;
            }
            if ($productData['deal_value']<0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Deal value should not be less than 0.")
                );
            }
            if ($productData['deal_discount_type']=="percent" && $productData['deal_value']>100) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of percent type discount, the deal value should not be more than 100.")
                );
            } elseif ($productData['deal_discount_type']=="fixed"
                        && $productData['deal_value']>$productData['price']) {
   
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of fixed type discount, the deal value should not be more than product price.")
                );
            } elseif ($productData['deal_discount_type']=="fixed" &&
            $productData['deal_value']==$productData['price']
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of fixed type discount, the deal value should not be same with regular price.")
                );
            } elseif ($productData['deal_discount_type']=="percent" &&
            $productData['deal_value']==100
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("In case of Percent type discount, the deal value should not be 100 percent.")
                );
            }
            $configTimeZone = $this->localeDate->getConfigTimezone();
            $defaultTimeZone = $this->localeDate->getDefaultTimezone();
    
            $dealToDate = $productData['deal_to_date_tmp'];
            $dealFromDate = $productData['deal_from_date_tmp'];
            $dealToDate = $dealToDate == '' ? $productData['deal_to_date'] : $dealToDate;
            $dealFromDate = $dealFromDate == '' ? $productData['deal_from_date'] : $dealFromDate;
    
            if ($dealToDate != '' && $dealFromDate != '') {
                $product->setDealFromDate($dealFromDate);
                $product->setDealToDate($dealToDate);
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
            }
        } elseif ($product->getEntityId() && $modEnable) {
            $proDealStatus = $this->productRepository->getById($product->getEntityId())->getDealStatus();
            //To Do for default special price of magneto
            if ($proDealStatus) {
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
                $product->setDealDiscountPercentage('');
            }
        }
        return $this;
    }
}
