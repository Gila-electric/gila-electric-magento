<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Company;

use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Plugin\Checkout\Controller\Index\IndexPlugin;

class IsPaymentActiveForCurrentUser
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(CompanyContext $companyContext)
    {
        $this->companyContext = $companyContext;
    }

    /**
     * Check is payment method active for current user.
     *
     * @param string $methodCode
     * @return bool
     */
    public function execute(string $methodCode): bool
    {
        $company = $this->companyContext->getCurrentCompany();
        if (!$company->getCompanyId()) {
            return true;
        }

        return $company->getCompanyId()
            && $company->isActive()
            && $this->companyContext->isResourceAllow(IndexPlugin::RESOURCE)
            && !in_array($methodCode, $company->getRestrictedPayments(true), true);
    }
}
