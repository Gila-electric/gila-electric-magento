<?php
namespace Ignitix\QuickOrder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Check extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $stockRegistry;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $productInput = $this->getRequest()->getParam('product');
        $qty = (int) $this->getRequest()->getParam('qty');

        try {
            $product = $this->productRepository->get($productInput);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => 'Product not found.']);
        }

        if ($product->getTypeId() !== 'simple') {
            return $result->setData(['success' => false, 'message' => 'Only simple products allowed.']);
        }

        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        if (!$stockItem->getIsInStock() || $stockItem->getQty() < $qty) {
            return $result->setData(['success' => false, 'message' => 'Insufficient stock.']);
        }

        return $result->setData(['success' => true]);
    }
}
