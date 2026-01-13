<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Category\Item;

use Amasty\QuickOrder\Model\Import\Provider\Configurable\Provider as ConfigurableOption;
use Amasty\QuickOrder\Model\Item\Validation\Validator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\StoreManagerInterface;

class Manager
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Fields which always must be rewrite in update item process.
     *
     * @var array
     */
    private $rewritableFields;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRegistry $stockRegistry,
        Session $session,
        Validator $validator,
        StoreManagerInterface $storeManager,
        array $rewritableFields = []
    ) {
        $this->session = $session;
        $this->validator = $validator;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->rewritableFields = $rewritableFields;
    }

    public function getItems(): array
    {
        return $this->session->getItems();
    }

    public function getItem(int $itemId): array
    {
        return $this->session->getItem($itemId);
    }

    public function update(int $itemId, array $itemData): array
    {
        $currentItemData = $this->session->getItem($itemId);

        if (!$itemData['checked']) {
            $this->removeItem($itemId);
        } else {
            foreach ($this->rewritableFields as $rewritableField) {
                unset($currentItemData[$rewritableField]);
            }
            foreach ($itemData as $key => $value) {
                $currentItemData[$key] = $value;
            }

            $result = $this->validator->validate($currentItemData);
            switch ($result->getStatusCode()) {
                case Validator::NOT_CONFIGURED:
                    $errors = explode("\n", $result->getMessage());
                    $errors = array_unique($errors);
                    $errors = array_values($errors);
                    $currentItemData['errors'] = $errors;
                    break;
                case Validator::SUCCESS:
                    $currentItemData['errors'] = [];
                    break;
            }
            foreach ($result->getProductData() as $key => $value) {
                $currentItemData[$key] = $value;
            }

            $currentItemData = $this->updateStockStatus($currentItemData);

            $this->session->setItem($currentItemData['id'], $currentItemData);
        }

        return $currentItemData;
    }

    public function removeItem(int $itemId): void
    {
        $this->session->removeItem($itemId);
    }

    public function clear(): void
    {
        $this->session->clear();
    }

    private function updateStockStatus(array $currentItemData): array
    {
        $stockStatus = $this->stockRegistry->getStockStatus(
            $this->getSimpleProduct((int) $currentItemData['id'], $currentItemData)->getId(),
            $this->storeManager->getStore()->getWebsiteId()
        );

        $currentItemData['available_qty'] = $stockStatus->getQty();
        $currentItemData['stock_status'] = $stockStatus->getStockStatus();

        return $currentItemData;
    }

    private function getSimpleProduct(int $productId, array $itemData): ProductInterface
    {
        $product = $this->productRepository->getById($productId);
        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                $simpleProduct = $product->getTypeInstance()->getProductByAttributes(
                    $itemData[ConfigurableOption::REQUEST_CODE] ?? [],
                    $product
                ) ?: $product;
                break;
            default:
                $simpleProduct = $product;
        }

        return $simpleProduct;
    }
}
