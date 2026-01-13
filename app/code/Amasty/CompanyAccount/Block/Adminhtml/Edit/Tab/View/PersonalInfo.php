<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Block\Adminhtml\Edit\Tab\View;

use Amasty\CompanyAccount\Block\Adminhtml\AbstractInfo;
use Magento\Customer\Controller\RegistryConstants;

class PersonalInfo extends AbstractInfo
{
    /**
     * @return string
     */
    public function getLabel(): string
    {
        return parent::getLabel() . ':';
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->customerDataProvider->getCompanyNameByCustomerId((int)$this->getCustomerId());
    }

    /**
     * @return int|null
     */
    protected function getCustomerId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
}
