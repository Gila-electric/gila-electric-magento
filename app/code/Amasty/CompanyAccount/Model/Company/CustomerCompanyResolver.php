<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Company;

use Amasty\CompanyAccount\Api\Data\CustomerInterface as CompanyCustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerCompanyResolver
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function resolveForCustomerId(int $customerId): ?CompanyCustomerInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $extension = $customer->getExtensionAttributes();

        if (!$extension) {
            return null;
        }

        /** @var CompanyCustomerInterface|null $amcompanyAttributes */
        $amcompanyAttributes = $extension->getAmcompanyAttributes();

        if (!$amcompanyAttributes || !$amcompanyAttributes->getCompanyId()) {
            return null;
        }

        return $amcompanyAttributes;
    }
}
