<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Api\Import;

interface ResourceInterface
{
    /**
     * @param array $skuArray
     * @return array
     */
    public function execute(array $skuArray = []): array;
}
