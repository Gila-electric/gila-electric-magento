<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\Collection as CreditEventCollection;

interface GetEventsByCreditIdInterface
{
    public function execute(int $creditId): CreditEventCollection;
}
