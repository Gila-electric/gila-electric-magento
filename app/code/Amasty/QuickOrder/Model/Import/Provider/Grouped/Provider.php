<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Provider\Grouped;

use Amasty\QuickOrder\Model\Import\Provider\AbstractOptionProvider;

class Provider extends AbstractOptionProvider
{
    public const TYPE = 'super_group';
    public const REQUEST_CODE = 'super_group';

    /**
     * @inheritDoc
     */
    public function getOption(string $title, string $sku)
    {
        return $this->getOptionCache()[$title] ?? null;
    }

    /**
     * For Grouped Provider value is Child product qty.
     *
     * @inheritDoc
     */
    public function getValue(string $title, string $sku, ?string $optionId = null)
    {
        return $title;
    }
}
