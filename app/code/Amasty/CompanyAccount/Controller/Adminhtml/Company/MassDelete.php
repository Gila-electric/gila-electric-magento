<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Magento\Framework\Data\Collection\AbstractDb;

class MassDelete extends \Amasty\CompanyAccount\Controller\Adminhtml\Company\MassActionAbstract
{
    protected function doAction(AbstractDb $collection)
    {
        $collectionSize = $collection->getSize();
        foreach ($collection as $company) {
            /**
             * @TODO be shure, that all customer of company will be blocked
             */
            $this->companyRepository->delete($company);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
    }
}
