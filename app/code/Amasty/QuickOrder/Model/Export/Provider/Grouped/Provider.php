<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export\Provider\Grouped;

use Amasty\QuickOrder\Model\Export\Provider\OptionProvider;

class Provider extends OptionProvider
{
    public function getValue(string $optionId): ?string
    {
        return (string) $optionId;
    }
}
