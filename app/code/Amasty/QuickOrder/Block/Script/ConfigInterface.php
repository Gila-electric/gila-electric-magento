<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Script;

interface ConfigInterface
{
    public function getJsonConfig(): string;

    public function setItemId(int $itemId): void;

    public function getItemId(): int;

    /**
     * @return string
     */
    public function toHtml();
}
