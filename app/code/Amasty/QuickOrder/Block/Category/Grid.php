<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Category;

use Amasty\QuickOrder\Model\Category\ProductProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Grid extends Template
{
    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    public function __construct(
        ProductProvider $productProvider,
        Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->productProvider = $productProvider;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        $this->jsLayout = $this->updateItems($this->jsLayout);

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    public function isComponentExist(string $component): bool
    {
        return isset($this->jsLayout['components'][$component]);
    }

    /**
     * Push to component saved items.
     * @param array $jsLayout
     * @return array
     */
    public function updateItems(array $jsLayout): array
    {
        if (isset($jsLayout['components']['grid']['config'])) {
            $jsLayout['components']['grid']['config']['itemStorage']['items'] = $this->productProvider
                ->convertProductData($this->getProductCollection());
            $jsLayout['components']['grid']['config']['allItems'] = [];
        }

        return $jsLayout;
    }

    public function getProductCollection(): ProductCollection
    {
        return $this->productCollection;
    }

    public function setProductCollection(ProductCollection $productCollection): void
    {
        $this->productCollection = $productCollection;
    }
}
