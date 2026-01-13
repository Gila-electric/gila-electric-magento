<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Setup\Patch\Data;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Api\Data\OrderInterface as SalesOrder;

class InsertCompanyOrders implements DataPatchInterface
{
    private const SALES_ORDER_TABLE = 'sales_order';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $orderTable = $this->resourceConnection->getTableName(self::SALES_ORDER_TABLE);
        $companyOrdersTable = $this->resourceConnection->getTableName(OrderInterface::TABLE_NAME);
        $companyOrdersSelect = $connection->select()->from($companyOrdersTable);

        if (!$connection->fetchRow($companyOrdersSelect)) {
            $select = $connection->select()
                ->from($orderTable, [OrderInterface::COMPANY_ORDER_ID => SalesOrder::ENTITY_ID])
                ->joinInner(
                    ['customer' => $this->resourceConnection->getTableName(CustomerInterface::TABLE_NAME)],
                    sprintf(
                        '%s.%s = customer.%s',
                        $orderTable,
                        SalesOrder::CUSTOMER_ID,
                        CustomerInterface::CUSTOMER_ID
                    ),
                    [CustomerInterface::COMPANY_ID]
                )
                ->joinInner(
                    ['company' => $this->resourceConnection->getTableName(CompanyInterface::TABLE_NAME)],
                    sprintf(
                        'customer.%s = company.%s',
                        CustomerInterface::COMPANY_ID,
                        CompanyInterface::COMPANY_ID
                    ),
                    [CompanyInterface::COMPANY_NAME]
                );

            if ($data = $connection->fetchAll($select)) {
                $connection->insertOnDuplicate(
                    $this->resourceConnection->getTableName(OrderInterface::TABLE_NAME),
                    $data
                );
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
