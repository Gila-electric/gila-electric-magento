<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Api\Export;

interface ProviderInterface
{
    /**
     * @param array $skuArray
     */
    public function initData(array $skuArray): void;

    /**
     * @param int $optionId
     * @return string|null
     */
    public function getOption(int $optionId): ?string;

    /**
     * @param string $optionId
     * @return string|null
     */
    public function getValue(string $optionId): ?string;
}
