<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Setup\Patch\Data;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerData implements DataPatchInterface
{
    public const BATCH_SIZE = 250;

    private const CUSTOMER_ENTITY_ID = 'entity_id';
    private const CUSTOMER_IS_ACTIVE = 'is_active';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

    public function __construct(
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory,
        QueryGenerator $queryGenerator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
        $this->queryGenerator = $queryGenerator;
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $companyAccCustomerTable = $this->resourceConnection->getTableName(CustomerInterface::TABLE_NAME);
        $companyAccCustomerSelect = $connection->select()->from($companyAccCustomerTable);

        if (!$connection->fetchRow($companyAccCustomerSelect)) {
            $customers = [];

            try {
                $customerCollection = $this->collectionFactory->create();

                /** @var Select $select */
                $select = $customerCollection->getSelect();

                $select->reset(Select::COLUMNS)->columns(
                    [
                        self::CUSTOMER_ENTITY_ID,
                        self::CUSTOMER_IS_ACTIVE
                    ]
                );

                $batchSelectIterator = $this->queryGenerator->generate(
                    $customerCollection->getIdFieldName(),
                    $select,
                    self::BATCH_SIZE
                );
            } catch (LocalizedException $e) {
                $batchSelectIterator = [];
            }

            foreach ($batchSelectIterator as $query) {
                foreach ($connection->fetchAll($query) as $customer) {
                    $customers[] = [
                        CustomerInterface::CUSTOMER_ID => $customer[self::CUSTOMER_ENTITY_ID],
                        CustomerInterface::STATUS => $customer[self::CUSTOMER_IS_ACTIVE] ?? 1
                    ];
                }

                if ($customers) {
                    $connection->insertMultiple(
                        $companyAccCustomerTable,
                        $customers
                    );

                    $customers = [];
                }
            }
        }

        return $this;
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
