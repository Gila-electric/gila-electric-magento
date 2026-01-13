<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByIdCache implements GetByIdInterface
{
    /**
     * @var array
     */
    private $credits = [];

    /**
     * @var GetById
     */
    private $getById;

    public function __construct(GetById $getById)
    {
        $this->getById = $getById;
    }

    /**
     * @param int $id
     * @return CreditInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): CreditInterface
    {
        if (!isset($this->credits[$id])) {
            $this->credits[$id] = $this->getById->execute($id);
        }

        return $this->credits[$id];
    }
}
