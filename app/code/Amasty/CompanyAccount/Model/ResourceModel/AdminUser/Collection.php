<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\ResourceModel\AdminUser;

class Collection extends \Magento\User\Model\ResourceModel\User\Collection
{
    /**
     * @param array $userIds
     * @return $this
     */
    public function addIdsFilter($userIds = [])
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $this->getSelect()->where('main_table.user_id in (?)', $userIds);

        return $this;
    }
}
