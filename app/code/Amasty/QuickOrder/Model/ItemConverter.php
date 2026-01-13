<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Amasty\QuickOrder\Model\Import\Provider\Configurable\Provider as ConfigurableOption;
use Amasty\QuickOrder\Model\Item\Block\Renderer as OptionsRenderer;
use Amasty\QuickOrder\Model\Item\OptionCounter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Locale\Format as LocaleFormat;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Helper\Data as WeeeHelper;

class ItemConverter
{
    /**
     * @var OptionCounter
     */
    private $optionCounter;

    /**
     * @var OptionsRenderer
     */
    private $optionsRenderer;

    /**
     * @var Image
     */
    private $imageModel;

    /**
     * @var WeeeHelper
     */
    private $weeeHelper;

    /**
     * @var LocaleFormat
     */
    private $localeFormat;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $stockStatuses;

    public function __construct(
        OptionCounter $optionCounter,
        OptionsRenderer $optionsRenderer,
        Image $imageModel,
        WeeeHelper $weeeHelper,
        LocaleFormat $localeFormat,
        StockRegistry $stockRegistry,
        StoreManagerInterface $storeManager
    ) {
        $this->optionCounter = $optionCounter;
        $this->optionsRenderer = $optionsRenderer;
        $this->imageModel = $imageModel;
        $this->weeeHelper = $weeeHelper;
        $this->localeFormat = $localeFormat;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Convert Product model into array data , used on frontend grid component.
     *
     * @param int $itemId
     * @param ProductInterface|Product $product
     * @return array
     */
    public function convert(int $itemId, ProductInterface $product): array
    {
        if ($product->getOptions() === null) {
            // fix for options renderer
            // possible fatal in Magento/Catalog/view/frontend/templates/product/view/options.phtml
            // when execute count(null)
            $product->setOptions([]);
        }

        $this->optionsRenderer->setProduct($product);
        $optionCount = $this->optionCounter->get($product);

        $productUrl = $product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE
            ? $product->getProductUrl()
            : '';

        $stockStatus = $this->getStockStatus($product);

        $image = $this->imageModel->init($product);

        return [
            'id' => $itemId,
            'product_id' => $product->getId(),
            'product_url' => $productUrl,
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'type_id' => $product->getTypeId(),
            'isWeee' => (bool) $this->weeeHelper->getAmountExclTax($product),
            'qty' => $stockStatus->getStockStatus() ? 1 : 0,
            'min_qty' => $stockStatus->getStockStatus() ? 1 : 0,
            'options_html' => $optionCount ? ($this->optionsRenderer->getOptionsHtml($itemId) ?: null) : null,
            'options_count' => $optionCount,
            'image'=> $image->getUrl(),
            'image_label' => $image->getLabel(),
            'price'=> $this->optionsRenderer->getPriceHtml($itemId),
            'final_price' => $this->localeFormat->getNumber(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount()
            ),
            'base_price' => $this->localeFormat->getNumber(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
            ),
            'available_qty' => $stockStatus->getQty(),
            'stock_status' => $stockStatus->getStockStatus(),
            'rating' => $this->getRatingData($product),
            'errors' => []
        ];
    }

    public function getStockStatus(Product $product): StockStatusInterface
    {
        if (!isset($this->stockStatuses[$product->getId()])) {
            $this->stockStatuses[$product->getId()] = $this->stockRegistry->getStockStatus(
                $product->getId(),
                $this->storeManager->getStore()->getWebsiteId()
            );
        }

        return $this->stockStatuses[$product->getId()];
    }

    public function resolveSimpleProduct(Product $product, array $itemData): Product
    {
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

    public function getRatingData(Product $product): array
    {
        $ratingSummary = $product->getRatingSummary();
        $ratingValue = $ratingSummary['rating_summary'] ?? $ratingSummary;
        $reviewCount = $ratingSummary['reviews_count'] ?? $product->getReviewsCount();

        if ($reviewCount && $ratingValue) {
            $rating = [
                'value' => (float) $ratingValue,
                'count' => (int) $reviewCount
            ];
        } else {
            $rating = ['count' => 0];
        }

        return $rating;
    }
}
