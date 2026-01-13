<?php

/**
 * Webkul_DailyDeals ListProduct collection block.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Reports\Model\ResourceModel\Product as ReportsProducts;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers as SalesReportFactory;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productFactory;

    /**
     * @var Magento\Reports\Model\ResourceModel\Product
     */
    private $reportproductsFactory;

    /**
     * @var Magento\Sales\Model\ResourceModel\Report\Bestsellers
     */
    private $salesReportFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param CollectionFactory $productFactory
     * @param ReportsProducts\CollectionFactory $reportproductsFactory
     * @param SalesReportFactory\CollectionFactory $salesReportFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Webkul\DailyDeals\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productFactory,
        ReportsProducts\CollectionFactory $reportproductsFactory,
        SalesReportFactory\CollectionFactory $salesReportFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Webkul\DailyDeals\Helper\Data $helperData
    ) {
        $this->productFactory = $productFactory;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->reportproductsFactory = $reportproductsFactory;
        $this->salesReportFactory = $salesReportFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper
        );
        $this->helperData = $helperData;
        $this->today = $this->_localeDate->convertConfigTimeToUtc($this->_localeDate->date());
    }

    /**
     * Get Product Collection
     *
     * @return void
     */
    protected function _getProductCollection()
    {
        if (!$this->_productCollection) {
            $paramData = $this->getRequest()->getParams();
            $productname = $this->getRequest()->getParam('name');
            $isModEnable = $this->helperData->isModEnable();
            $allProductIds = [];
            if ($isModEnable) {
                $simpledealIds = $this->productFactory
                    ->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('type_id', ['nin'=>'configurable'])
                    ->addFieldToFilter('deal_status', 1)
                    ->addFieldToFilter('visibility', ['neq' => 1])
                    ->addAttributeToFilter(
                        'deal_from_date',
                        ['lt'=>$this->today]
                    )->addAttributeToFilter(
                        'deal_to_date',
                        ['gt'=>$this->today]
                    )->getColumnValues('entity_id');
                
                $notvisibleIds = $this->productFactory
                    ->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('deal_status', 1)
                    ->addFieldToFilter('visibility', ['eq' => 1])
                    ->addAttributeToFilter(
                        'deal_from_date',
                        ['lt'=>$this->today]
                    )->addAttributeToFilter(
                        'deal_to_date',
                        ['gt'=>$this->today]
                    )->getColumnValues('entity_id');
                
                $configurableIds = [];
                $groupproIds= [];
                $groupnotvisible=[];
                foreach ($simpledealIds as $notvisiblesimpleIds) {
                    $groupparentIds = $this->grouped->getParentIdsByChild($notvisiblesimpleIds);
                    $groupproIds= $groupparentIds;
                }
                $groupproIds = array_merge($groupproIds, $simpledealIds);
                foreach ($notvisibleIds as $notvisibleId) {
                    $parentIds = $this->configurable->getParentIdsByChild($notvisibleId);
                    $groupparentnotvisible = $this->grouped->getParentIdsByChild($notvisibleId);
                    if (!empty($groupparentnotvisible)) {
                        $groupnotvisible [] = $groupparentnotvisible;
                    }
                  
                    $configurableIds[] = $parentIds[0];
                }
                $allProductIds = array_merge($simpledealIds, $configurableIds, $groupproIds, $groupnotvisible);
            }

            $collection = $this->productFactory
                            ->create()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('entity_id', ['in'=>$allProductIds]);
            $layer = $this->getLayer();

            $origCategory = "";

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
           
            $this->_productCollection = $collection;
           
            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
            
            $toolbar = $this->getToolbarBlock();
            
            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );
           
        }
        $this->_productCollection->getSize();
        return $this->_productCollection;
    }

    /**
     * Compare Deal Discount Percentage
     *
     * @param array $a
     * @param array $b
     * @return void
     */
    public function compareDealDiscountPercentage($a, $b)
    {
        return strcmp($a['deal_discount_percentage'], $b['deal_discount_percentage']);
    }

    /**
     * Get top deals of day
     *
     * @return void
     */
    public function getTopDealsOfDay()
    {
        $productCollection = $this->_productCollection;
        $newProCollection = [];
       
        foreach ($productCollection as $product) {
            $type = $product->getTypeId();
            $productId = $product->getId();
            
            if ($type == 'configurable') {
                $_configChild = $product->getTypeInstance()->getUsedProductIds($product);
                $getChildId = [];
                foreach ($_configChild as $child) {
                    $getChildId[] = $child;
                }

                $maxVal = -99999999;
                $dealDetail = $this->productFactory
                                        ->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('entity_id', ['in'=>$getChildId]);
                foreach ($dealDetail as $singleChild) {
                    if (isset($singleChild['deal_discount_percentage'])
                    && $singleChild['deal_discount_percentage'] &&
                    $singleChild['deal_discount_percentage']>=$maxVal) {
                        $maxVal = $singleChild['deal_discount_percentage'];
                    }
                }
                    $product['deal_discount_percentage'] = $maxVal;
                    $newProCollection [] = $product;
            } elseif ($type == 'grouped') {
               
                $_groupChild = $product->getTypeInstance()->getAssociatedProducts($product);

                $getChildId = [];
                foreach ($_groupChild as $child) {
                    $getChildId[] = $child['entity_id'];
                }

                $maxVal = -99999999;
                $dealDetail = $this->productFactory
                                        ->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('entity_id', ['in'=>$getChildId]);
                foreach ($dealDetail as $singleChild) {
                    if (isset($singleChild['deal_discount_percentage']) &&
                    $singleChild['deal_discount_percentage']
                    && $singleChild['deal_discount_percentage']>=$maxVal) {
                        $maxVal = $singleChild['deal_discount_percentage'];
                    }
                }
                   $product['deal_discount_percentage'] = $maxVal;
                   $newProCollection [] = $product;
            } else {
                $newProCollection [] = $product;
            }
            uasort($newProCollection, [$this, 'compareDealDiscountPercentage']);
        }
        return array_slice(array_reverse($newProCollection), 0, 5, true);
    }

    /**
     * GetDealProductImage
     *
     * @param Magento\Catalog\Model\Product $product
     * @return string product image url
     */
    public function getDealProductImage($product)
    {
        return $this->_imageHelper->init($product, 'category_page_grid')->constrainOnly(false)
                                        ->keepAspectRatio(true)
                                        ->keepFrame(false)
                                        ->resize(400)
                                        ->getUrl();
    }

    /**
     * GetTopDealViewsProduct
     *
     * @return ReportsProducts
     */
    public function getTopDealViewsProduct()
    {
        return $this->reportproductsFactory->create()->addAttributeToSelect('*')
                                    ->addViewsCount()
                                    ->setStoreId(0)
                                    ->addStoreFilter(0)
                                    ->addAttributeToFilter('deal_status', 1)
                                    ->addAttributeToFilter('deal_from_date', ['lt'=>$this->today])
                                    ->addAttributeToFilter('deal_to_date', ['gt'=>$this->today])
                                    ->setPageSize(5);
    }

    /**
     * Get Top Sale Product
     *
     * @return void
     */
    public function getTopSaleProduct()
    {
        $productIds = $this->salesReportFactory->create()->setModel(\Magento\Catalog\Model\Product::class)
                                            ->addStoreFilter(0)->setPageSize(1000)->getColumnValues('product_id');
        if (empty($productIds)) {
            $productIds = [0];
        }
        return $this->reportproductsFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('entity_id', ['in' => $productIds])
                        ->addAttributeToFilter('deal_status', 1)
                        ->addAttributeToFilter('deal_from_date', ['lt'=>$this->today])
                        ->addAttributeToFilter('deal_to_date', ['gt'=>$this->today])
                        ->addFieldToFilter('visibility', ['neq' => 1])
                        ->setPageSize(5);
    }
}
