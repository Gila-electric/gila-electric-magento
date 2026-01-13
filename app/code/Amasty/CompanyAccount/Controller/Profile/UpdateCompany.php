<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Controller\Profile;

class UpdateCompany extends SaveCompany
{
    public const RESOURCE = 'Amasty_CompanyAccount::edit_account';
    public const REDIRECT_URL = 'amasty_company/profile/edit';

    /**
     * @var string
     */
    protected $redirectUrl = self::REDIRECT_URL;

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isResourceAllow(static::RESOURCE);
    }
}
