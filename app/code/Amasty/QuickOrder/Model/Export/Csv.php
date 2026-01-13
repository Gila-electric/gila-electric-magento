<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export;

use Amasty\QuickOrder\Api\Export\HandlerInterface;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Csv implements HandlerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExportHandler
     */
    private $exportHandler;

    public function __construct(
        ExportHandler $exportHandler,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->exportHandler = $exportHandler;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFile(): array
    {
        $uniqueName = microtime();
        $file = sprintf('export/quickorder-%s.csv', $uniqueName);

        try {
            $writer = $this->getWriter();
            $writer->create('export');
            $stream = $writer->openFile($file, 'w+');
            $stream->lock();

            $exportData = $this->exportHandler->getExportData();
            $stream->writeCsv($exportData->getHeaders());
            foreach ($exportData->getRows() as $row) {
                $stream->writeCsv($row);
            }

            $stream->unlock();
            $stream->close();
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
            $this->throwException($e->getMessage());
        }

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @param string $message
     * @throws RuntimeException
     */
    protected function throwException(string $message)
    {
        throw new RuntimeException($message);
    }

    /**
     * @return WriteInterface
     * @throws FileSystemException
     */
    protected function getWriter(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }
}
