<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Api;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface CustomerRepositoryInterface
{
    /**
     * @param int $customerId
     * @return CustomerInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $customerId): CustomerInterface;

    /**
     * @param int $companyId
     * @param int[] $customerIds
     * @return void
     * @throws LocalizedException
     */
    public function assignToCompany(int $companyId, array $customerIds): void;

    /**
     * @param int $customerId
     * @return void
     * @throws Exception
     */
    public function delete(int $customerId): void;
}
