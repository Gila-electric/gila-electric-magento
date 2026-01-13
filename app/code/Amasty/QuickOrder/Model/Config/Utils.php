<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Config;

class Utils
{
    /**
     * @param string $multiselectValue
     * @return array
     */
    public function parseMultiselect(string $multiselectValue): array
    {
        return array_filter(
            explode(',', $multiselectValue),
            function ($elem) {
                return trim($elem) != '';
            }
        );
    }
}
