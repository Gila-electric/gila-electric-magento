<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;

/**
 * Retrieve amount in display currency.
 */
class RetrieveAmount
{
    public function execute(CreditEventInterface $creditEvent): float
    {
        $amount = $creditEvent->getAmount();
        if ($creditEvent->getRate()) {
            $amount *= $creditEvent->getRate();
        }

        return $amount;
    }
}
