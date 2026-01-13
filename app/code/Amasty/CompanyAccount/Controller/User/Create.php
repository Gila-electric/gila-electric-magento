<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Controller\User;

class Create extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::users_add';

    /**
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('New User'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isActiveOrInactiveCompany()
            && parent::isAllowed();
    }
}
