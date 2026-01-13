<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\CustomerRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Model\Customer;
use Amasty\CompanyAccount\Model\CustomerFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Customer as CustomerResource;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var array
     */
    private $customers;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        CustomerResource $customerResource,
        CustomerFactory $customerFactory
    ) {
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $customerId): CustomerInterface
    {
        if (!isset($this->customers[$customerId])) {
            /** @var Customer $customer */
            $customer = $this->customerFactory->create();
            $this->customerResource->load($customer, $customerId);
            if (!$customer->getCustomerId()) {
                throw new NoSuchEntityException(__('Customer with specified ID "%1" not found.', $customerId));
            }
            $this->customers[$customerId] = $customer;
        }

        return $this->customers[$customerId];
    }

    /**
     * @param int $companyId
     * @param int[] $customerIds
     * @return void
     * @throws LocalizedException
     */
    public function assignToCompany(int $companyId, array $customerIds): void
    {
        $this->customerResource->assignCompany($companyId, $customerIds);
    }

    /**
     * @param int $customerId
     * @return void
     * @throws Exception
     */
    public function delete(int $customerId): void
    {
        $this->customerResource->delete($this->getById($customerId));
    }
}
