<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Source;

use Amasty\QuickOrder\Api\Source\SourceReaderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;

class SourceReaderAdapter
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        array $config = []
    ) {
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * @param string $type
     * @param string $fileName
     * @param array $config
     * @return SourceReaderInterface
     * @throws FileSystemException
     */
    public function getReader(string $type, string $fileName, array $config = []): SourceReaderInterface
    {
        if (!isset($this->config[$type]['readerClass'])) {
            throw new \RuntimeException('No reader config for type: ' . $type);
        }

        $readerClass = $this->config[$type]['readerClass'];

        if (!is_subclass_of($readerClass, SourceReaderInterface::class)) {
            throw new \RuntimeException('Wrong source reader class: "' . $readerClass);
        }

        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $fileReader = $directoryRead->openFile($directoryRead->getRelativePath($fileName));

        return $this->objectManager->create(
            $readerClass,
            [
                'fileReader' => $fileReader,
                'config'     => $config
            ]
        );
    }
}
