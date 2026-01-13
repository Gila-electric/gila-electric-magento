<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface GetByCompanyIdInterface
{
    /**
     * @param int $companyId
     * @return CreditInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $companyId): CreditInterface;
}
