<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Block\Account;

class Delimiter extends \Magento\Customer\Block\Account\Delimiter
{
    private const LINKS_RESOURCES = [
        'Amasty_CompanyAccount::view_account',
        'Amasty_CompanyAccount::users_view',
        'Amasty_CompanyAccount::roles_view',
        'Amasty_CompanyAccount::orders_all_view',
        'Amasty_CompanyAccount::use_credit'
    ];

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $context = $this->getData('companyContext');
        $html = '';
        if ($context->isCreateCompanyAllowed()
            || ($context->isCurrentUserCompanyUser() && $this->isLinkResourceAllowed())
        ) {
            $html = parent::_toHtml();
        }

        return $html;
    }

    /**
     * Determine is some of company links allowed by ACL
     *
     * @return bool
     */
    private function isLinkResourceAllowed(): bool
    {
        $allowed = false;

        $context = $this->getData('companyContext');

        foreach (self::LINKS_RESOURCES as $linkResource) {
            if ($allowed = $context->isResourceAllow($linkResource)) {
                break;
            }
        }

        return $allowed;
    }
}
