<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Command;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface SaveInterface
{
    /**
     * @param CreditInterface $credit
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(CreditInterface $credit): void;
}
