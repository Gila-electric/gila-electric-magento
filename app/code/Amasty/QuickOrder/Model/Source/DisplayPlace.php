<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DisplayPlace implements OptionSourceInterface
{
    public const PAGE_HEADER = 0;
    public const TOP_MENU = 1;
    public const PAGE_FOOTER = 2;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PAGE_HEADER,
                'label' => __('Page Header')
            ],
            [
                'value' => self::TOP_MENU,
                'label' => __('Top Menu')
            ],
            [
                'value' => self::PAGE_FOOTER,
                'label' => __('Page Footer')
            ]
        ];
    }
}
