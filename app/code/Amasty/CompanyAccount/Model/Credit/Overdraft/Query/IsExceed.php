<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Model\ResourceModel\Overdraft\IsOverdraftExceed;

class IsExceed implements IsExceedInterface
{
    /**
     * @var IsOverdraftExceed
     */
    private $isOverdraftExceed;

    public function __construct(IsOverdraftExceed $isOverdraftExceed)
    {
        $this->isOverdraftExceed = $isOverdraftExceed;
    }

    public function execute(int $creditId): bool
    {
        return $this->isOverdraftExceed->execute($creditId);
    }
}
