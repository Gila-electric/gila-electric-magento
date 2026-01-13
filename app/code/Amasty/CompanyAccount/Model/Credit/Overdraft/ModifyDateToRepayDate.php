<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Overdraft;

use Amasty\CompanyAccount\Model\Source\Credit\OverdraftRepayType;
use DateTime;

class ModifyDateToRepayDate
{
    public function execute(DateTime $dateTime, int $digit, int $digitType): void
    {
        switch ($digitType) {
            case OverdraftRepayType::YEAR:
                $type = 'year';
                break;
            case OverdraftRepayType::MONTH:
                $type = 'month';
                break;
            case OverdraftRepayType::DAY:
            default:
                $type = 'day';
        }

        $modifyExpression = sprintf('+%d %s', $digit, $type);
        $dateTime->modify($modifyExpression);
    }
}
