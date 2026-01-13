<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event\Query;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditEventInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;

class CreateCreditEvent implements CreateCreditEventInterface
{
    /**
     * @var CreditEventInterfaceFactory
     */
    private $creditEventFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        CreditEventInterfaceFactory $creditEventFactory,
        Json $jsonSerializer
    ) {
        $this->creditEventFactory = $creditEventFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(array $data): CreditEventInterface
    {
        if (isset($data[CreditEventInterface::COMMENT]) && is_array($data[CreditEventInterface::COMMENT])) {
            $data[CreditEventInterface::COMMENT] = $this->jsonSerializer->serialize(
                $data[CreditEventInterface::COMMENT]
            );
        }

        return $this->creditEventFactory->create(['data' => $data]);
    }
}
