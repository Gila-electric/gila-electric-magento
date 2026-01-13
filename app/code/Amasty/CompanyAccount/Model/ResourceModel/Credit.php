<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Credit extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(CreditInterface::MAIN_TABLE, CreditInterface::ID);
    }
}
