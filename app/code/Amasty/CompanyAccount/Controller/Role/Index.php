<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Controller\Role;

use Amasty\CompanyAccount\Controller\AbstractAction;
use Magento\Framework\App\ResponseInterface;

class Index extends AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::roles_view';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Company Roles'));

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
