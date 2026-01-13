<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\FormatComments;

class RetrieveComments
{
    /**
     * @var FormatComments
     */
    private $formatComments;

    public function __construct(FormatComments $formatComments)
    {
        $this->formatComments = $formatComments;
    }

    public function execute(CreditEventInterface $creditEvent): string
    {
        $result = '';
        if ($creditEvent->getComment()) {
            $result = $this->formatComments->execute($creditEvent->getComment());
        }

        return $result;
    }
}
