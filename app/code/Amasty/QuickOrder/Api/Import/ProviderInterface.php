<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Api\Import;

interface ProviderInterface
{
    /**
     * @param array $skuArray
     */
    public function initData(array $skuArray);

    /**
     * @param string $title
     * @param string $sku
     * @return string|null
     */
    public function getOption(string $title, string $sku);

    /**
     * @param string $title
     * @param string $sku
     * @return string|null
     */
    public function getValue(string $title, string $sku, ?string $optionId = null);

    /**
     * @return string
     */
    public function getCode();
}
