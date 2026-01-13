<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Plugin\Customer\Model\ResourceModel\Grid;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Customer\Model\ResourceModel\Grid\Collection;

class CollectionPlugin
{
    public function beforeAddFieldToFilter(Collection $subject, $field, $condition = null): array
    {
        if ($field == CompanyInterface::COMPANY_NAME) {
            $field = 'company.' . $field;
        }

        if ($field == 'main_table.' . CompanyInterface::COMPANY_NAME) {
            $field = 'company.' . CompanyInterface::COMPANY_NAME;
        }

        return [$field, $condition];
    }
}
