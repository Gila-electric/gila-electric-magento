<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Observer\Adminhtml;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface as CompanyCustomerInterface;
use Amasty\CompanyAccount\Model\Company\CustomerCompanyResolver;
use Amasty\CompanyAccount\Model\Company\IsResourceAllowed;
use Amasty\CompanyAccount\Model\Payment\ConfigProvider;
use Magento\Backend\Model\Session\Quote as AdminQuote;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\MethodInterface;

/**
 * adminhtml observer
 * event payment_method_is_active
 * observer name Amasty_CompanyAccount::LockAdminPayments
 */
class PaymentMethodIsActive implements ObserverInterface
{
    private const IS_AVAILABLE = 'is_available';

    /**
     * @var AdminQuote
     */
    private $quote;

    /**
     * @var IsResourceAllowed
     */
    private $isResourceAllowed;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CustomerCompanyResolver
     */
    private $attributesResolver;

    public function __construct(
        AdminQuote $quote,
        IsResourceAllowed $isResourceAllowed,
        CompanyRepositoryInterface $companyRepository,
        CustomerCompanyResolver $attributesResolver
    ) {
        $this->quote = $quote;
        $this->isResourceAllowed = $isResourceAllowed;
        $this->companyRepository = $companyRepository;
        $this->attributesResolver = $attributesResolver;
    }

    public function execute(Observer $observer): void
    {
        /** @var MethodInterface $methodInstance */
        $methodInstance = $observer->getData('method_instance');
        /** @var DataObject $resultObject */
        $resultObject = $observer->getData('result');

        if ($resultObject->getData(self::IS_AVAILABLE) && $this->isMethodRestricted($methodInstance->getCode())) {
            $resultObject->setData(
                self::IS_AVAILABLE,
                false
            );
        }
    }

    /**
     * Return true for restricted payment method
     */
    private function isMethodRestricted(string $methodCode): bool
    {
        $isCompanyCredit = $methodCode === ConfigProvider::METHOD_NAME;
        $amcompanyAttributes = $this->resolveAmcompanyAttributes();
        if ($amcompanyAttributes === null) {
            return $isCompanyCredit;
        }
        try {
            $company = $this->companyRepository->getById($amcompanyAttributes->getCompanyId());
        } catch (NoSuchEntityException $e) {
            return $isCompanyCredit;
        }

        if (!$company->isActive() || in_array($methodCode, $company->getRestrictedPayments(true), true)) {
            return true;
        }

        if ($isCompanyCredit
            && $this->isCompanyCreditRestricted((int) $amcompanyAttributes->getRoleId())
        ) {
            return true;
        }

        return false;
    }

    private function resolveAmcompanyAttributes(): ?CompanyCustomerInterface
    {
        $customerId = $this->quote->getCustomerId();
        if (!$customerId) {
            return null;
        }

        return $this->attributesResolver->resolveForCustomerId((int)$customerId);
    }

    private function isCompanyCreditRestricted(int $roleId): bool
    {
        return $roleId && !$this->isResourceAllowed->checkResourceForRole(ConfigProvider::ACL_RESOURCE, $roleId);
    }
}
