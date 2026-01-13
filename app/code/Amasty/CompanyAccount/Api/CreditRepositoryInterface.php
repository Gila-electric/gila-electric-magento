<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Api;

/**
 * @api
 */
interface CreditRepositoryInterface
{
    /**
     * @return \Amasty\CompanyAccount\Api\Data\CreditInterface
     */
    public function getNew(): \Amasty\CompanyAccount\Api\Data\CreditInterface;

    /**
     * @param int $companyId
     * @return \Amasty\CompanyAccount\Api\Data\CreditInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCompanyId(int $companyId): \Amasty\CompanyAccount\Api\Data\CreditInterface;

    /**
     * @param \Amasty\CompanyAccount\Api\Data\CreditInterface $credit
     * @return void
     */
    public function save(\Amasty\CompanyAccount\Api\Data\CreditInterface $credit): void;
}
