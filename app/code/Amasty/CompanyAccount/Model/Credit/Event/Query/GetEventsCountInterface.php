<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event\Query;

/**
 * @api
 */
interface GetEventsCountInterface
{
    public function execute(int $creditId, ?string $eventType = null): int;
}
