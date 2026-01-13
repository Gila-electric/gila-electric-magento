<?php
namespace Ignitix\QuickOrder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Psr\Log\LoggerInterface;

class Suggest extends Action
{
    protected $resultJsonFactory;
    protected $productCollectionFactory;
    protected $storeManager;
    protected $priceHelper;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->logger = $logger;
    }

    public function execute()
	{
		$result = $this->resultJsonFactory->create();
		$query = $this->getRequest()->getParam('query');
		$storeId = (int)$this->getRequest()->getParam('store');
	
		$collection = $this->productCollectionFactory->create();
		$collection->setStoreId($storeId)
			->addAttributeToSelect(['name', 'sku', 'thumbnail', 'price', 'special_price'])
			->addStoreFilter($storeId)
			->addAttributeToFilter('type_id', 'simple')
			->addAttributeToFilter([
				['attribute' => 'sku', 'like' => "%$query%"],
				['attribute' => 'name', 'like' => "%$query%"]
			])
			->setPageSize(10);
	
		$mediaUrl = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	
		$items = [];
		foreach ($collection as $product) {
			$priceValue = $product->getFinalPrice();
			$price = $this->priceHelper->currency($priceValue, true, false);
			$items[] = [
				'name' => $product->getName(),
				'sku' => $product->getSku(),
				'thumbnail' => $mediaUrl . 'catalog/product' . $product->getThumbnail(),
				'price' => $price
			];
		}
	
		return $result->setData($items);
	}
}