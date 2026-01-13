<?php
/**
 * Webkul_DailyDeals Collection controller.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;
use Magento\Bundle\Model\Product\Type as bundle;

class GetTimes extends Action
{
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Bundle
     */
    private $bundle;
    /**
     * @var \Magento\GroupedProductt\Model\Product\Type\Grouped
     */
    private $grouped;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    private $_coreRegistry;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $dailyDealHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $today;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Block\Product\Context $cont
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     * @param \Magento\Bundle\Model\Product\Type $bundle
     * @param Grouped $grouped
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Block\Product\Context $cont,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper,
        \Magento\Bundle\Model\Product\Type $bundle,
        Grouped $grouped
    ) {
        $this->_coreRegistry = $cont->getRegistry();
        $this->dailyDealHelper = $dailyDealHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->bundle = $bundle;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->today = $timezone->convertConfigTimeToUtc($timezone->date());
        parent::__construct($context);
    }

    /**
     * DailyDeals Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $curPro = $this->_coreRegistry->registry('current_product');
            $collection = $this->getProductCollection();
            $result = [];
            foreach ($collection as $key => $value) {
                $product = $this->productFactory->create()->load($value->getId());
                $dealDetail  = $this->getCurrentProductDealDetail($value);
                if ($value['type_id']=="grouped") {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    $parent = $this->grouped->getParentIdsByChild($dealDetail['entity_id']);
                    $result[$parent[0]] = $dealDetail;
                    $dealDetail['parent'] = $parent;
                    $result[$dealDetail['entity_id']] = $dealDetail;
                } elseif ($value['type_id']=="configurable") {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    foreach ($dealDetail as $deal) {
                        $parent= $this->configurable->getParentIdsByChild($deal['entity_id']);
                        $result[$parent[0]] = $dealDetail;
                        $dealDetail['parent'] = $parent;
                        $result[$deal['entity_id']] = $dealDetail;
                    }
                } elseif ($value['type_id']=="bundle") {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    foreach ($dealDetail as $deal) {
                        $parent=$this->bundle->getParentIdsByChild($deal['entity_id']);
                        $result[$parent[0]] = $dealDetail;
                         $dealDetail['parent'] = $parent;
                        $result[$deal['entity_id']] = $dealDetail;
                    }
                } else {
                    $dealDetail  = $this->getCurrentProductDealDetail($value);
                    $result[$dealDetail['entity_id']] = $dealDetail;
                }
            }
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->dailyDealHelper
                ->jsonEncode(
                    [
                        'success' => 1,
                        'data' => $result
                    ]
                ));
        } catch (\Exception $e) {
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->dailyDealHelper
                ->jsonEncode(
                    [
                        'success' => 0,
                        'message' => __('Something went wrong in getting spin wheel.')
                    ]
                ));
        }
    }

    /**
     * Get Product collection
     *
     * @return void
     */
    protected function getProductCollection()
    {
        $simpledealIds = $this->productCollectionFactory
                                    ->create()
                                    ->addAttributeToSelect('*')
                                    ->addAttributeToFilter('type_id', ['nin'=>'configurable'])
                                    ->addAttributeToFilter('deal_status', 1)
                                    ->addFieldToFilter('visibility', ['neq' => 1])
                                    ->addAttributeToFilter(
                                        'deal_from_date',
                                        ['lt'=>$this->today]
                                    )->addAttributeToFilter(
                                        'deal_to_date',
                                        ['gt'=>$this->today]
                                    )->getColumnValues('entity_id');
        $notvisibleIds = $this->productCollectionFactory
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
        $groupnotvisible= [];
        
        foreach ($simpledealIds as $notvisiblesimpleIds) {
            $groupparentIds = $this->grouped->getParentIdsByChild($notvisiblesimpleIds);
            $groupproIds= $groupparentIds;
        }
        $groupproIds = array_merge($groupproIds, $simpledealIds);
        
        foreach ($notvisibleIds as $notvisibleId) {
            $parentIds = $this->configurable->getParentIdsByChild($notvisibleId);
            $groupparentnotvisible = $this->grouped->getParentIdsByChild($notvisibleId);
            $groupnotvisible [] = $groupparentnotvisible;
            $configurableIds[] = $parentIds;
        }
        $allProductIds = array_merge($simpledealIds, $configurableIds, $groupproIds, $groupnotvisible);
        if (!empty($notvisibleIds)) {
            $allProductIds = array_merge($allProductIds, $notvisibleIds);

        }
       
        $collection = $this->productCollectionFactory
                                ->create()
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter('entity_id', ['in'=>$allProductIds]);

        return $collection;
    }

    /**
     * Get Currenct Product Detail
     *
     * @param collection $curPro
     * @return void
     */
    public function getCurrentProductDealDetail($curPro)
    {
        
        $productType = $curPro->getTypeId();
        $assDealDetails = [];
        if ($productType == "configurable") {
            $dataDeal = $this->getConfigAssociateProDeals($curPro);
        } elseif ($productType == "bundle") {
            
            $dataDeal = $this->getBundleAssociateProDeals($curPro);
            
        } elseif ($productType == "grouped") {
            $dataDeal = $this->getGroupAssociateProDeals($curPro);
        } else {
            $dataDeal = $this->dailyDealHelper->getProductDealDetail($curPro);
            
            if ($dataDeal) {
                $dataDeal['entity_id'] = $curPro->getId();
            }
        }
        return $dataDeal;
    }
    /**
     * Get Product Association Pro Deals
     *
     * @param collection $curPro
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
        
            $dealDetail = $this->dailyDealHelper->getMaxDiscount($assDealDetails);
            if (!empty($dealDetail)) {
                return $dealDetail;
            }
        }
        return false;
    }
    /**
     * Get bundle Associate Pro Deals
     *
     * @param object $curPro
     * @return void
     */
    public function getBundleAssociateProDeals($curPro)
    {
        
        $bundleProId = $curPro->getId();
        $empty = [];
        $alldeal = [];
        $assDealDetails = [];
        $associatedProducts = $this->bundle->getChildrenIds($bundleProId);
        foreach ($associatedProducts as $key => $value) {
            foreach ($value as $val) {
                $dealDetail = $this->getChildProductDealDetail($val);
                if ($dealDetail
                && isset($dealDetail['deal_status'])
                && $dealDetail['deal_status']
                && isset($dealDetail['diff_timestamp'])
                ) {
                    $alldeal[$val] = $dealDetail['saved-amount'];
                    $dealDetail['entity_id'] = $val;
                    $dealDetail['pro_type'] = 'bundle';
                    $assDealDetails[$val] = $dealDetail;
                }
            }
        }
        return $assDealDetails;
    }
    
    /**
     * Get Config Associate Pro Deals
     *
     * @param object $curPro
     * @return void
     */
    public function getConfigAssociateProDeals($curPro)
    {
        $configProId = $curPro->getId();
        $alldeal = [];
        $associatedProducts = $this->configurable->getChildrenIds($configProId);
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
        return $assDealDetails;
    }

    /**
     * GetChild Product DealDetail
     *
     * @param int $proId
     * @return Magento\Catalog\Model\Product
     */
    public function getChildProductDealDetail($proId)
    {
        $product = $this->productFactory->create()->load($proId);
        $dealvalue = $product->getDealValue();
        if ($product->getDealDiscountType() == 'percent') {
            $price = $product->getPrice() * ($dealvalue/100);
            $discount = $dealvalue;
        } else {
            $price = $dealvalue;
            $discount = ($dealvalue/$product->getPrice())*100;
        }
        $dealData = $this->dailyDealHelper->getProductDealDetail($product);
        if (!isset($dealData['discount-percent'])) {
            
            $dealData['discount-percent'] = round(100-$discount);
        }
        return $dealData;
    }
}
