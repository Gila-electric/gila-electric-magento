<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Catalog\Block\Product;

use Amasty\QuickOrder\Block\Category\ProductList;
use Amasty\QuickOrder\Model\CategoryMode;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Module\Manager as ModuleManager;

class ListProductPlugin
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function aroundToHtml(ListProduct $productList, callable $proceed): string
    {
        if ($productList->getMode() === CategoryMode::QUICKORDER_MODE) {
            /** @var ProductList $categoryModeBlock */
            $categoryModeBlock = $productList->getLayout()->getBlock('amquickorder.category.mode');
            if ($categoryModeBlock) {
                // fix for m240 (toolbar block must be configured, but addToolbarBlock called only in _beforeToHtml,
                // instead of early versions, when addToolbarBlock called in initializeProductCollection)
                $productList->setTemplate('') && $proceed();
                $categoryModeBlock->setProductCollection($productList->getLoadedProductCollection());
                $result = $categoryModeBlock->toHtml();
                if ($this->isShopbyEnabled()) {
                    $result = $this->wrapForShopby($result);
                }
            }
        }

        return $result ?? $proceed();
    }

    private function isShopbyEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_Shopby');
    }

    private function wrapForShopby(string $content): string
    {
        return sprintf('<div id="amasty-shopby-product-list">%s</div>', $content);
    }
}
