<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Ui\DataProvider\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $creditId = (int) $this->request->getParam(CreditEventInterface::CREDIT_ID);
            $this->filterBuilder->setField(CreditEventInterface::CREDIT_ID);
            $this->filterBuilder->setValue($creditId);
            $this->addFilter($this->filterBuilder->create());
        }

        return parent::getSearchCriteria();
    }
}
