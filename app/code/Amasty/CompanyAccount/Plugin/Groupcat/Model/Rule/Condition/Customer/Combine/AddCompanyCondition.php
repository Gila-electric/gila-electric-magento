<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Plugin\Groupcat\Model\Rule\Condition\Customer\Combine;

use Amasty\CompanyAccount\Model\Condition\Company as CompanyCondition;
use Amasty\Groupcat\Model\Rule\Condition\Customer\Combine;

class AddCompanyCondition
{
    public function afterGetNewChildSelectOptions(Combine $subject, array $result): array
    {
        $attributes = array_pop($result);
        if (isset($attributes['value'])) {
            $attributes['value'][] = [
                'label' => __('Company'),
                'value' => CompanyCondition::class
            ];
            $result[] = $attributes;
        }

        return $result;
    }
}
