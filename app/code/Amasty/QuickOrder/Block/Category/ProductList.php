<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Category;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\View\Element\Template;

class ProductList extends Template
{
    public const TOOLBAR_NAME = 'product_list_toolbar';

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var string
     */
    protected $_template = 'Amasty_QuickOrder::catalog/product/category/list.phtml';

    public function getGridHtml(): string
    {
        /** @var Grid $grid */
        if ($grid = $this->getChildBlock('grid')) {
            $grid->setProductCollection($this->getProductCollection());
        }

        return $grid ? $grid->toHtml() : '';
    }

    /**
     * @param bool $isBottom
     * @return string
     */
    public function getToolbarHtml(bool $isBottom = false): string
    {
        $toolbarBlock = $this->getLayout()->getBlock(static::TOOLBAR_NAME);

        if ($toolbarBlock) {
            $toolbarBlock->setIsBottom($isBottom);
            $html = $toolbarBlock->toHtml();
        }

        return $html ?? '';
    }

    /**
     * @return ProductCollection
     */
    public function getProductCollection(): ProductCollection
    {
        return $this->productCollection;
    }

    /**
     * @param ProductCollection $productCollection
     */
    public function setProductCollection(ProductCollection $productCollection): void
    {
        $this->productCollection = $productCollection;
    }
}
