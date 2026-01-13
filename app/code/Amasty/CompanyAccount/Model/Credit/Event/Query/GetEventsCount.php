<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event\Query;

use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\GetEventsCount as GetEventsCountResource;

class GetEventsCount implements GetEventsCountInterface
{
    /**
     * @var GetEventsCountResource
     */
    private $getEventsCountResource;

    public function __construct(GetEventsCountResource $getEventsCountResource)
    {
        $this->getEventsCountResource = $getEventsCountResource;
    }

    public function execute(int $creditId, ?string $eventType = null): int
    {
        return $this->getEventsCountResource->execute($creditId, $eventType);
    }
}
