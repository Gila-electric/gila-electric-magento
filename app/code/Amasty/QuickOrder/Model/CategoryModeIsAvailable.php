<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

class CategoryModeIsAvailable implements IsAvailableInterface
{
    /**
     * @var CategoryMode
     */
    private $categoryMode;

    public function __construct(CategoryMode $categoryMode)
    {
        $this->categoryMode = $categoryMode;
    }

    public function execute(): bool
    {
        return $this->categoryMode->isAvailable();
    }
}
