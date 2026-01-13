<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Category;

use Amasty\QuickOrder\Model\ItemConverter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class ProductProvider
{
    /**
     * @var ItemConverter
     */
    private $itemConverter;

    public function __construct(ItemConverter $itemConverter)
    {
        $this->itemConverter = $itemConverter;
    }

    public function convertProductData(ProductCollection $collection): array
    {
        $collection->addOptionsToResult();

        $productsData = [];

        /** @var Product $product */
        foreach ($collection as $product) {
            $productsData[] = $this->itemConverter->convert((int) $product->getId(), $product);
        }

        return $productsData;
    }
}
