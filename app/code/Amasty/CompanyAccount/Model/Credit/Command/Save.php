<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Credit\Command;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit;
use Amasty\CompanyAccount\Model\ResourceModel\Credit as CreditResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var CreditResource
     */
    private $creditResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CreditResource $creditResource,
        LoggerInterface $logger
    ) {
        $this->creditResource = $creditResource;
        $this->logger = $logger;
    }

    /**
     * @param CreditInterface|Credit $credit
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(CreditInterface $credit): void
    {
        try {
            $this->creditResource->save($credit);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Credit'), $e);
        }
    }
}
