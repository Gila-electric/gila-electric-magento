<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item\Validation;

use Amasty\QuickOrder\Model\ResourceModel\Inventory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

class Validator
{
    public const SUCCESS = 0;

    public const NOT_CONFIGURED = 1;

    public const ERROR = 2;

    /**
     * @var array|null
     */
    private $stockInfo;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Result
     */
    private $result;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterface[]
     */
    private $products = [];

    public function __construct(
        Inventory $inventory,
        StoreManagerInterface $storeManager,
        Result $result,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
        $this->result = $result;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
    }

    public function init(array $values, string $field)
    {
        foreach ($this->getStockInfo($values, $field) as $sku => $value) {
            $this->stockInfo[mb_strtolower($sku)] = $value;
        }

        foreach ($this->getProductsByField($values, $field) as $product) {
            $this->products[mb_strtolower($product->getSku())] = $product;
        }
    }

    private function getProductsByField(array $values, string $field): Collection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setFlag('has_stock_status_filter', true);
        $productCollection->addFieldToFilter($field, $values);
        $productCollection->addStoreFilter($this->storeManager->getStore());
        $productCollection->addAttributeToSelect('links_purchased_separately');
        $productCollection->addOptionsToResult();

        return $productCollection;
    }

    /**
     * Returning validation object with code , message , product id
     *
     * @param array $itemData
     *
     * @return Result
     */
    public function validate(array $itemData): Result
    {
        $this->result->setStatusCode(self::SUCCESS);
        $this->result->setMessage('');

        $productSku = $itemData['sku'] ?? '';

        /** @var Product $product */
        $product = $this->getProduct($productSku);
        if (!$product) {
            return $this->getErrorResponse(__('Product doesn\'t exist'));
        }

        if ((int)$product->getStatus() === Status::STATUS_ENABLED) {
            if ($this->getStockStatus($productSku)) {
                $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
                    new DataObject($itemData),
                    $product,
                    AbstractType::PROCESS_MODE_FULL
                );

                /**
                 * Error message
                 */
                if (is_string($cartCandidates) || $cartCandidates instanceof Phrase) {
                    if ($cartCandidates instanceof Phrase) {
                        $cartCandidates = $cartCandidates->render();
                    }

                    $this->result->setStatusCode(self::NOT_CONFIGURED);
                    $errors = explode("\n", $cartCandidates);
                    $error = reset($errors);
                    $this->result->setMessage($error);
                }

                $this->result->setProductId($product->getId());
                $this->result->setProductData([
                    'product_name' => $product->getName(),
                    'product_url' => $product->getProductUrl()
                ]);
            } else {
                $this->getErrorResponse(__('Out of Stock'));
            }
        } else {
            $this->getErrorResponse(__('Disabled'));
        }

        return $this->result;
    }

    protected function getProduct(string $sku):? Product
    {
        if (!empty($this->products)) {
            $product = $this->products[mb_strtolower($sku)] ?? null;
        } else {
            try {
                $product = $this->productRepository->get($sku);

                if (!in_array($this->storeManager->getStore()->getId(), $product->getStoreIds())) {
                    $product = null;
                }
            } catch (NoSuchEntityException $ex) {
                $product = null;
            }
        }

        return $product;
    }

    /**
     * @param Phrase $message
     *
     * @return Result
     */
    private function getErrorResponse(Phrase $message)
    {
        $this->result->setStatusCode(self::ERROR);
        $this->result->setMessage($message->render());
        $this->result->setProductId(null);

        return $this->result;
    }

    private function getStockStatus(string $productSku): int
    {
        $stockStatus = 0;
        $preparedSku = mb_strtolower($productSku);

        if (isset($this->stockInfo[$preparedSku])) {
            $stockStatus = $this->stockInfo[$preparedSku];
        } else {
            $stockInfo = $this->getStockInfo([$productSku], 'sku');

            if (!empty($stockInfo)) {
                $this->stockInfo[$preparedSku] = reset($stockInfo);
                $stockStatus = $this->stockInfo[$preparedSku];
            }
        }

        return (int) $stockStatus;
    }

    /**
     * @param array $values
     * @param string $field
     * @return array
     */
    private function getStockInfo(array $values, string $field): array
    {
        return $this->inventory->getStockInfo(
            $values,
            $field,
            $this->storeManager->getStore()->getWebsite()->getCode()
        );
    }
}
