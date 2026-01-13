<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Company;

use Laminas\Stdlib\Exception\LogicException;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerNameResolver
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $customerNameGeneration
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerNameGeneration = $customerNameGeneration;
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws LocalizedException
     */
    public function getCustomerById($customerId)
    {
        $customer = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new LogicException(__('Customer does not exist'));
        }

        return $customer;
    }

    /**
     * @param $customerId
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerName($customerId)
    {
        return $this->customerNameGeneration->getCustomerName($this->getCustomerById($customerId));
    }
}
