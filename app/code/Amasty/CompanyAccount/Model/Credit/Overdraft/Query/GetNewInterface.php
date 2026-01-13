<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;

/**
 * @api
 */
interface GetNewInterface
{
    public function execute(): OverdraftInterface;
}
