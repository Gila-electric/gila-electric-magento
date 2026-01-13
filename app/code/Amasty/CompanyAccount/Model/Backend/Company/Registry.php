<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Backend\Company;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class Registry
{
    /**
     * @var CompanyInterface|null
     */
    private $company;

    public function set(CompanyInterface $company): void
    {
        $this->company = $company;
    }

    public function get(): CompanyInterface
    {
        return $this->company;
    }
}
