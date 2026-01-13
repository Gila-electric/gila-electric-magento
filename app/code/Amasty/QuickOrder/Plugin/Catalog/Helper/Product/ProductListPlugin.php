<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Catalog\Helper\Product;

use Amasty\QuickOrder\Model\CategoryMode as CategoryModeProvider;
use Amasty\QuickOrder\Model\Source\CategoryMode;
use Magento\Catalog\Helper\Product\ProductList;

class ProductListPlugin
{
    /**
     * @var CategoryModeProvider
     */
    private $categoryMode;

    public function __construct(CategoryModeProvider $categoryMode)
    {
        $this->categoryMode = $categoryMode;
    }

    public function afterGetAvailableViewMode(ProductList $subject, ?array $modes): ?array
    {
        if ($this->categoryMode->isAvailable()) {
            $quickOrderValue = $this->categoryMode->getValue();
            if ($this->categoryMode->getReplacementType() === CategoryMode::YES) {
                $modes = $quickOrderValue;
            } else {
                $modes = $modes ?? [];
                $modes = array_merge($modes, $quickOrderValue);
            }
        }

        return $modes;
    }

    public function beforeGetDefaultViewMode(ProductList $subject, ?array $options): ?array
    {
        if ($this->categoryMode->isAvailable()
            && $this->categoryMode->getReplacementType() === CategoryMode::AS_DEFAULT
        ) {
            $options = $this->categoryMode->getValue();
        }

        return [$options];
    }

    public function afterGetAvailableLimit(ProductList $subject, array $limit, string $mode): array
    {
        if ($mode === CategoryModeProvider::QUICKORDER_MODE) {
            $limit = $this->categoryMode->getAvailableLimit();
        }

        return $limit;
    }

    /**
     * @param ProductList $subject
     * @param string|int $defaultLimit
     * @param string $mode
     * @return string|int
     */
    public function afterGetDefaultLimitPerPageValue(ProductList $subject, $defaultLimit, string $mode)
    {
        if ($mode === CategoryModeProvider::QUICKORDER_MODE) {
            $defaultLimit = $this->categoryMode->getDefaultLimit();
        }

        return $defaultLimit;
    }
}
