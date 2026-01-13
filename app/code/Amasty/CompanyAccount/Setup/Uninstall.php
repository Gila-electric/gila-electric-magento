<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Setup;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\Data\PermissionInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public const TABLES_TO_DROP = [
        CompanyInterface::TABLE_NAME,
        CustomerInterface::TABLE_NAME,
        RoleInterface::TABLE_NAME,
        PermissionInterface::TABLE_NAME,
        OrderInterface::TABLE_NAME,
        CreditEventInterface::MAIN_TABLE,
        CreditInterface::MAIN_TABLE,
        OverdraftInterface::MAIN_TABLE
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->uninstallTables($setup);
        $this->uninstallConfigData($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): void
    {
        foreach (self::TABLES_TO_DROP as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): void
    {
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE 'amasty_companyaccount%'");
    }
}
