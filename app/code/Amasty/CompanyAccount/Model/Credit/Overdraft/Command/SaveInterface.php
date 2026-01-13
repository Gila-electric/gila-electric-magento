<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Command;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface SaveInterface
{
    /**
     * @param OverdraftInterface $overdraft
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(OverdraftInterface $overdraft): void;
}
