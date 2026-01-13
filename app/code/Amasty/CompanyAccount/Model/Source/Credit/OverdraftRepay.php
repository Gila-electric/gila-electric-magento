<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Source\Credit;

use Magento\Framework\Data\OptionSourceInterface;

class OverdraftRepay implements OptionSourceInterface
{
    public const UNLIMITED = 0;
    public const SET = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::UNLIMITED,
                'label' => __('Unlimited')
            ],
            [
                'value' => self::SET,
                'label' => __('Set')
            ]
        ];
    }
}
