<?php
/**
 * Webkul DailyDeals DI
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Plugin\Block;
 
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigProType;
use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;

class ProductListCompare
{
    /**
     * @var ConfigProType
     */
    public $configProType;
    /**
     * @var Product
     */
    public $product;
    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    public $dailyDealHelper;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    public $layout;
    
    /**
     * Constructor
     *
     * @param ConfigProType $configProType
     * @param Product $product
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param Grouped $grouped
     * @param array $data
     */
    public function __construct(
        ConfigProType $configProType,
        Product $product,
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper,
        \Magento\Framework\View\LayoutInterface $layout,
        public Grouped $grouped,
        array $data = []
    ) {
        $this->configProType = $configProType;
        $this->product = $product;
        $this->dailyDealHelper = $dailyDealHelper;
        $this->layout = $layout;
    }
    /**
     * Around Get Product Price
     *
     * @param \Magento\Catalog\Block\Product\Compare\ListCompare $list
     * @param obejct $proceed
     * @param obejct $product
     * @param string $idSuffix
     * @return void
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\Compare\ListCompare $list,
        $proceed,
        $product,
        $idSuffix = ''
    ) {
        $dealDetail = $this->getCurrentProductDealDetail($product);
        $dealDetailHtml = "";
        if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
            $dealDetailHtml = $this->layout
            ->createBlock(\Webkul\DailyDeals\Block\Category\CategoryList::class)
            ->setTemplate('Webkul_DailyDeals::viewoncategory.phtml')->setDealDetail($dealDetail)->toHtml();
            ;
            if (!$product->getSpecialPrice() && isset($dealDetail['special-price'])) {
                $product->setSpecialPrice($dealDetail['special-price']);
            }
            return $proceed($product).$dealDetailHtml;
        }
        return $proceed($product);
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
     * Get Current Product Deal Detail
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
     * GetConfig Associate ProDeals
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
            if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
                $alldeal[$assProId] = $dealDetail['saved-amount'];
                $dealDetail['entity_id'] = $assProId;
                $dealDetail['pro_type'] = 'configurable';
                $assDealDetails[$assProId] = $dealDetail;
            }
        }
        if (!empty($assDealDetails)) {
            $maxsIndex = array_keys($assDealDetails, max($assDealDetails));
            $dealDetail = isset($assDealDetails[$maxsIndex[0]]) ? $assDealDetails[$maxsIndex[0]] : false;
            return $dealDetail;
        }
        return false;
    }

    /**
     * GetChild Product DealDetail
     *
     * @param int $proId
     * @return Magento\Catalog\Model\Product
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->product->load($proId);
        return $this->dailyDealHelper->getProductDealDetail($product);
    }
}
