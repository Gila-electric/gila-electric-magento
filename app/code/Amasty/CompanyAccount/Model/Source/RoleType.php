<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Source;

class RoleType
{
    public const TYPE_ALL = 0;
    public const TYPE_DEFAULT_USER = 1;
    public const TYPE_DEFAULT_ADMINISTRATOR = 2;

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            [
                'value' => self::TYPE_ALL,
                'label' => __('Default')
            ],
            [
                'value' => self::TYPE_DEFAULT_USER,
                'label' => __('Default User')
            ],
            [
                'value' => self::TYPE_DEFAULT_ADMINISTRATOR,
                'label' => __('Default Administrator')
            ]
        ];
    }
}
