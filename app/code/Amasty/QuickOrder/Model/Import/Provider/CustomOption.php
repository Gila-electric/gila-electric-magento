<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Provider;

class CustomOption extends AbstractOptionProvider
{
    public const TYPE = 'custom_option';
    public const REQUEST_CODE = 'options';

    /**
     * @param string $title
     * @param string $sku
     * @return string|null
     */
    public function getOption(string $title, string $sku)
    {
        return $this->getOptionCache()[$title][$sku] ?? null;
    }

    /**
     * @param string $title
     * @param string $sku
     * @return string
     */
    public function getValue(string $title, string $sku, ?string $optionId = null)
    {
        return $this->getValueCache()[$title][$sku] ?? $title;
    }
}
