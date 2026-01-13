<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\Serialize\Serializer\Json;

class GetValue
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(CreditEventInterface $creditEvent, string $key): ?string
    {
        if ($creditEvent->getComment()) {
            $comments = $this->jsonSerializer->unserialize($creditEvent->getComment());
        }

        return $comments[$key] ?? null;
    }
}
