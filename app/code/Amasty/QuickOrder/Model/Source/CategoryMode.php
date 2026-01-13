<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CategoryMode implements OptionSourceInterface
{
    public const AS_DEFAULT = 0;
    public const USE_DEFAULT = 1;
    public const YES = 2;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::AS_DEFAULT,
                'label' => __('No (and use Table Mode as default)')
            ],
            [
                'value' => self::USE_DEFAULT,
                'label' => __('No (but use default settings)')
            ],
            [
                'value' => self::YES,
                'label' => __('Yes')
            ]
        ];
    }
}
