<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Provider\Configurable;

use Amasty\QuickOrder\Model\Import\Provider\AbstractOptionProvider;

class Provider extends AbstractOptionProvider
{
    public const TYPE = 'super_attribute';
    public const REQUEST_CODE = 'super_attribute';

    /**
     * @inheritDoc
     */
    public function getOption(string $title, string $sku)
    {
        return $this->getOptionCache()[$title] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $title, string $sku, ?string $optionId = null)
    {
        $value = array_filter($this->getValueCache(), function ($item) use ($title, $optionId) {
            return $item['title'] == $title && $item['attribute_id'] == $optionId;
        });

        return current(array_keys($value)) ?: null;
    }
}
