<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @api
 */
class AddComments
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(CreditEventInterface $creditEvent, array $newComments)
    {
        $comments = $creditEvent->getComment()
            ? $this->jsonSerializer->unserialize($creditEvent->getComment())
            : [];
        $comments += $newComments;
        $creditEvent->setComment($this->jsonSerializer->serialize($comments));
    }
}
