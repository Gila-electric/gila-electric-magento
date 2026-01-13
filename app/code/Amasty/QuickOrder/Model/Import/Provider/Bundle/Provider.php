<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Provider\Bundle;

use Amasty\QuickOrder\Model\Import\Provider\AbstractOptionProvider;

class Provider extends AbstractOptionProvider
{
    public const TYPE = 'selections';
    public const REQUEST_CODE = 'bundle_option';
    public const QTY_REQUEST_CODE = 'bundle_option_qty';

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
        return $this->getValueCache()[$title] ?? null;
    }
}
