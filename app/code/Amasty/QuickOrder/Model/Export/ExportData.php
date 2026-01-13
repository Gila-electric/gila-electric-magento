<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export;

class ExportData
{
    public const OPTION_HEADER_TEMPLATE = 'option_%s';

    /**
     * @var string[]
     */
    private $headers = [];

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var int
     */
    private $optionCounter = 0;

    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function addOptionHeader(int $optionNumber): void
    {
        if ($optionNumber > $this->optionCounter) {
            $this->optionCounter++;
            $this->addHeader(sprintf(static::OPTION_HEADER_TEMPLATE, $optionNumber));
        }
    }

    public function addRow(array $row)
    {
        $this->rows[] = $row;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
