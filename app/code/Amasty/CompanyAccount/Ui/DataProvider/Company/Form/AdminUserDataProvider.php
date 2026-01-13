<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace  Amasty\CompanyAccount\Ui\DataProvider\Company\Form;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class AdminUserDataProvider extends UserDataProvider
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExcludeCustomerIds()
    {
        $companyId = $this->request->getParam(CompanyInterface::COMPANY_ID);
        if ($companyId) {
            return $this->companyResource->getAllSuperUserIds([$companyId]);
        }

        return parent::getExcludeCustomerIds();
    }
}
