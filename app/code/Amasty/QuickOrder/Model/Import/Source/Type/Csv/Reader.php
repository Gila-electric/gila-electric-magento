<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Source\Type\Csv;

use Amasty\QuickOrder\Api\Source\SourceReaderInterface;
use Magento\Framework\Filesystem\File\ReadInterface as FileReader;

class Reader implements SourceReaderInterface
{
    public const TYPE_ID = 'csv';

    public const SETTING_MAX_LINE_LENGTH = 'line_length';
    public const SETTING_FIELD_DELIMITER = 'delimiter';
    public const SETTING_FIELD_ENCLOSURE_CHARACTER = 'enclosure';
    public const SETTING_ESCAPE_CHARACTER = 'escape';
    public const SETTING_HAS_HEADER_ROW = 'header_row';

    public const DEFAULT_SETTINGS = [
        self::SETTING_MAX_LINE_LENGTH           => 0,
        self::SETTING_FIELD_DELIMITER           => ',',
        self::SETTING_FIELD_ENCLOSURE_CHARACTER => '"',
        self::SETTING_ESCAPE_CHARACTER          => '\\',
        self::SETTING_HAS_HEADER_ROW            => true
    ];

    /**
     * @var array
     */
    private $config;

    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var array
     */
    private $columnNames;

    /**
     * @var int
     */
    private $columnCount;

    public function __construct(
        FileReader $fileReader,
        array $config
    ) {
        $this->fileReader = $fileReader;
        $this->config = array_merge(self::DEFAULT_SETTINGS, $config);

        if ($this->config[self::SETTING_HAS_HEADER_ROW]) {
            $this->columnNames = array_map('strtolower', $this->readRow());
        }
    }

    public function readRow()
    {
        $rowData = $this->fileReader->readCsv(
            $this->config[self::SETTING_MAX_LINE_LENGTH],
            $this->config[self::SETTING_FIELD_DELIMITER],
            $this->config[self::SETTING_FIELD_ENCLOSURE_CHARACTER],
            $this->config[self::SETTING_ESCAPE_CHARACTER]
        );

        if (!is_array($rowData)) {
            return false;
        }

        if ($this->columnCount === null) {
            $this->columnCount = count($rowData);
        } elseif (count($rowData) != $this->columnCount) {
            $limitCount = min($this->columnCount, count($rowData));
            //throw new \RuntimeException('Bad input file format: Wrong column number');
        }

        if ($columnNames = $this->columnNames) {
            if (isset($limitCount)) {
                $columnNames = array_slice($columnNames, 0, $limitCount);
                $rowData = array_slice($rowData, 0, $limitCount);
            }
            $rowData = array_combine($columnNames, $rowData);
        }

        return $rowData;
    }

    public function estimateRecordsCount(): int
    {
        $position = $this->fileReader->tell();
        $rows = 0;
        $textBatch = '';
        while (!$this->fileReader->eof()) {
            $textBatch = $this->fileReader->readLine(1024 * 1024);
            $rows += substr_count($textBatch, PHP_EOL);
        }
        if (!empty($textBatch) && !in_array($textBatch[strlen($textBatch) - 1], ["\r", "\n"])) {
            $rows++;
        }
        $this->fileReader->seek($position);

        return $rows;
    }
}
