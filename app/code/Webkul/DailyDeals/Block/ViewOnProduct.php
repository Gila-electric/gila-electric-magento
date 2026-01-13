<?php

/**
 * Webkul_DailyDeals View On Product Block.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Block;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigProType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;
use Magento\Bundle\Model\Product\Type as bundle;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ViewOnProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Grouped
     */
    private $Grouped;
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Bundle
     */
    private $bundle;
    /**
     * @var \Magento\Catalog\Block\Product\Context\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configProType;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $helperData;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\DailyDeals\Helper\Data $helperData
     * @param ConfigProType $configProType
     * @param Grouped $grouped
     * @param Product $product
     * @param Bundle $bundle
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\DailyDeals\Helper\Data $helperData,
        ConfigProType $configProType,
        Grouped $grouped,
        Product $product,
        Bundle  $bundle,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->coreRegistry = $context->getRegistry();
        $this->helperData = $helperData;
        $this->configProType = $configProType;
        $this->bundle = $bundle;
        $this->grouped = $grouped;
        $this->product = $product;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get current product deal detail
     *
     * @return void
     */
    public function getCurrentProductDealDetail()
    {
        $curPro = $this->coreRegistry->registry('current_product');
        $productType = $curPro->getTypeId();
        $assDealDetails = [];
        if ($productType == "configurable") {
            $dataDeal = $this->getConfigAssociateProDeals(true);
        } elseif ($productType == "grouped") {
            $dataDeal = $this->getGroupAssociateProDeals(true);
        } else {
            $dataDeal = $this->helperData->getProductDealDetail($curPro);
            if ($dataDeal) {
                $dataDeal['entity_id'] = $curPro->getId();
            }
        }
        return $dataDeal;
    }

    /**
     * Get Child Product DealDetail
     *
     * @param int $proId
     * @return Magento\Catalog\Model\Product
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->productRepository->getById($proId);
        $dealvalue = $product->getDealValue();
        if ($product->getDealDiscountType() == 'percent') {
            $price = $product->getPrice() * ($dealvalue/100);
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        $dealData = $this->helperData->getProductDealDetail($product);
        
        if (!empty($dealData) && !empty($dealData['discount-percent'])) {
            $dealData['discount-percent'] = round(100-$discount);
        }
        return $dealData;
    }

    /**
     * Get Config getGroupAssociateProDeals
     *
     * @param boolean $max
     * @return void
     */
    public function getConfigAssociateProDeals($max = false)
    {
        $configProId = $this->coreRegistry->registry('current_product')->getId();
        $alldeal = [];
        $assDealDetails = [];
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
        $dealDetail = $this->helperData->getMaxDiscount($assDealDetails);
        if (!empty($dealDetail) && $max) {
            return $dealDetail;
        }
        return $assDealDetails;
    }
    
    /**
     * Get current product
     *
     * @return void
     */
    public function getCurrentProduct()
    {
        return $this->coreRegistry
                                ->registry('current_product');
    }
    /**
     * Get group associatedPro Deals
     *
     * @param boolean $max
     * @return void
     */
    public function getGroupAssociateProDeals($max = false)
    {
        $groupProId = $this->coreRegistry->registry('current_product')->getId();
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
        
            $dealDetail = $this->helperData->getMaxDiscount($assDealDetails);
            if (!empty($dealDetail) && $max) {
                return $dealDetail;
            }
        }
        return $assDealDetails;
    }
}
