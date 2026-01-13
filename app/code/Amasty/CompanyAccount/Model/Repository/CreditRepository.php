<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Command\SaveInterface as CommandSave;
use Amasty\CompanyAccount\Model\Credit\Query\GetByCompanyIdInterface;
use Amasty\CompanyAccount\Model\Credit\Query\GetNewInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CreditRepository implements CreditRepositoryInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var GetByCompanyIdInterface
     */
    private $getByCompanyId;

    /**
     * @var CommandSave
     */
    private $commandSave;

    public function __construct(
        GetNewInterface $getNew,
        GetByCompanyIdInterface $getByCompanyId,
        CommandSave $commandSave
    ) {
        $this->getNew = $getNew;
        $this->getByCompanyId = $getByCompanyId;
        $this->commandSave = $commandSave;
    }

    public function getNew(): CreditInterface
    {
        return $this->getNew->execute();
    }

    /**
     * @param int $companyId
     * @return CreditInterface
     * @throws NoSuchEntityException
     */
    public function getByCompanyId(int $companyId): CreditInterface
    {
        return $this->getByCompanyId->execute($companyId);
    }

    /**
     * @param CreditInterface $credit
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(CreditInterface $credit): void
    {
        $this->commandSave->execute($credit);
    }
}
