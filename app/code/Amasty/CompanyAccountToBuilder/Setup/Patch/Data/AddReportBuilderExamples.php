<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Company Account to Builder for Magento 2 (System)
*/

declare(strict_types=1);

namespace Amasty\CompanyAccountToBuilder\Setup\Patch\Data;

use Amasty\ReportBuilder\Model\Template\ExampleReport;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddReportBuilderExamples implements DataPatchInterface
{
    public const MODULE_NAME = 'Amasty_CompanyAccountToBuilder';

    /**
     * @var ExampleReport
     */
    private $exampleReport;

    public function __construct(
        ExampleReport $exampleReport
    ) {
        $this->exampleReport = $exampleReport;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddReportBuilderExamples
     */
    public function apply()
    {
        $this->exampleReport->createExampleReports(self::MODULE_NAME);

        return $this;
    }
}
