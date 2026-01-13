<?php
namespace Webkul\DailyDeals\Block\Plugin;

/**
 * Webkul DailyDeals ProductListUpdateForDeals plugin.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Catalog\Block\Product\ListProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigProType;
use Magento\Catalog\Model\ProductFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;

class ProductListUpdateForDeals
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configProType;
    
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Grouped
     */
    private $Grouped;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $dailyDealHelper;
    
    /**
     * @var Grouped
     */
    private $grouped;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;
    
    /**
     * Constructor
     *
     * @param ConfigProType $configProType
     * @param ProductFactory $product
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param Grouped $grouped
     */
    public function __construct(
        ConfigProType $configProType,
        ProductFactory $product,
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper,
        \Magento\Framework\View\LayoutInterface $layout,
        Grouped $grouped
    ) {
        $this->configProType = $configProType;
        $this->product = $product;
        $this->dailyDealHelper = $dailyDealHelper;
        $this->layout = $layout;
        $this->grouped = $grouped;
    }
 
    /**
     * Before get product price
     *
     * @param ListProduct $list
     * @param object $product
     * @return void
     */
    public function beforeGetProductPrice(
        ListProduct $list,
        $product
    ) {
        $dealDetail = $this->dailyDealHelper->getProductDealDetail($product);
    }

    /**
     * After get product price
     *
     * @param ListProduct $list
     * @param float $result
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function afterGetProductPrice(
        ListProduct $list,
        $result,
        $product
    ) {
        $dealDetail = $this->getCurrentProductDealDetail($product);
        $dealDetailHtml = "";
        if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
            $productlistBlock = $this->layout
            ->createBlock(\Webkul\DailyDeals\Block\Category\CategoryList::class)
            ->setTemplate('Webkul_DailyDeals::viewoncategory.phtml')->setDealDetail($dealDetail)->toHtml();
            if (!$product->getSpecialPrice() && isset($dealDetail['special-price'])) {
                $product->setSpecialPrice($dealDetail['special-price']);
            }
            return $result.$productlistBlock;
           
        }
        return $result;
    }

    /**
     * Get  Current Product Deal Detail
     *
     * @param object $curPro
     * @return void
     */
    public function getCurrentProductDealDetail($curPro)
    {
        $productType = $curPro->getTypeId();
        $assDealDetails = [];
        if ($productType == "configurable") {
            $dataDeal = $this->getConfigAssociateProDeals($curPro);
            
        } elseif ($productType == "grouped") {
            $dataDeal = $this->getGroupAssociateProDeals($curPro);
            if ($dataDeal) {
                $dataDeal['entity_id'] = $curPro->getId();
            }
            
        } else {
            $dataDeal = $this->dailyDealHelper->getProductDealDetail($curPro);
            if ($dataDeal) {
                $dataDeal['entity_id'] = $curPro->getId();
            }
        }
        return $dataDeal;
    }

    /**
     * Get Config Associate ProDeals
     *
     * @param Magento\Catalog\Model\Product $curPro
     * @return boolen|array
     */
    public function getConfigAssociateProDeals($curPro)
    {
        $configProId = $curPro->getId();
        $alldeal = [];
        $associatedProducts = $this->configProType->getChildrenIds($configProId);
        
        foreach ($associatedProducts[0] as $assProId) {
            $dealDetail = $this->getChildProductDealDetail($assProId);
            if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
            ) {
                $alldeal[$assProId] = $dealDetail['saved-amount'];
                $dealDetail['entity_id'] = $assProId;
                $dealDetail['pro_type'] = 'configurable';
                $assDealDetails[$assProId] = $dealDetail;
            }
        }
        if (isset($assDealDetails)) {
            $dealDetail = $this->dailyDealHelper->getMaxDiscount($assDealDetails);
            if (!empty($dealDetail)) {
                return $dealDetail;
            }
        }
        return false;
    }
    /**
     * Get group associated product deals
     *
     * @param object $curPro
     * @return void
     */
    public function getGroupAssociateProDeals($curPro)
    {
        $groupProId = $curPro->getId();
        $alldeal = [];
        $assDealDetails = [];
        $associatedProducts= $this->grouped->getChildrenIds($groupProId);
        foreach ($associatedProducts[3] as $assProId) {
            $dealDetail = $this->getChildProductDealDetail($assProId);
            if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
            ) {
                $alldeal[$assProId] = $dealDetail['saved-amount'];
                $dealDetail['entity_id'] = $assProId;
                $dealDetail['pro_type'] = 'grouped';
                $assDealDetails[$assProId] = $dealDetail;
            }
        }
            $dealDetail = $this->dailyDealHelper->getMaxDiscount($assDealDetails);
        if (!empty($dealDetail)) {
            return $dealDetail;
        }
        return false;
    }
    /**
     * Get Child Product Deal Details
     *
     * @param int $proId
     * @return Magento\Catalog\Model\Product
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->product->create()->load($proId);
        $dealvalue = $product->getDealValue();
        if ($product->getDealDiscountType() == 'percent') {
            $price = $product->getPrice() * ($dealvalue/100);
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        $dealData = $this->dailyDealHelper->getProductDealDetail($product);
        if (!empty($dealData) && !empty($dealData['discount-percent'])) {
            $dealData['discount-percent'] = round(100-$discount);
        }
        return $dealData;
    }
}
