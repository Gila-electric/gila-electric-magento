<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Category;

use Amasty\QuickOrder\Model\Category\Item\Manager as ItemManager;
use Amasty\QuickOrder\Model\Item\Block\Renderer as OptionsRenderer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;

class OptionsProvider
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var OptionsRenderer
     */
    private $optionsRenderer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ItemManager $itemManager,
        OptionsRenderer $optionsRenderer
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->itemManager = $itemManager;
        $this->optionsRenderer = $optionsRenderer;
    }

    public function getOptions(array $productIds): array
    {
        $optionsHtml = [];

        /** @var ProductInterface $product */
        foreach ($this->getProducts($productIds) as $product) {
            $productId = (int) $product->getId();

            $itemData = $this->itemManager->getItem($productId);

            if ($product->getOptions() === null) {
                // fix for options renderer
                // possible fatal in Magento/Catalog/view/frontend/templates/product/view/options.phtml
                // when execute count(null)
                $product->setOptions([]);
            }

            $product->setPreconfiguredValues(
                $product->processBuyRequest(new DataObject($itemData))
            );

            $this->optionsRenderer->setProduct($product);

            $optionsHtml[$productId] = $this->optionsRenderer->getOptionsHtml($productId);
        }

        return $optionsHtml;
    }

    /**
     * @param ProductInterface[] $productIds
     * @return array
     */
    private function getProducts(array $productIds): array
    {
        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');

        return $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
