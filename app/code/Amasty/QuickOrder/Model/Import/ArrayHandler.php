<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import;

use Amasty\QuickOrder\Model\Import\Source\Type\Range\Reader;
use Amasty\QuickOrder\Model\Import\Source\Type\Range\ReaderFactory;

class ArrayHandler
{
    /**
     * @var ReaderFactory
     */
    private $readerFactory;

    /**
     * @var ImportHandler
     */
    private $importHandler;

    public function __construct(
        ImportHandler $importHandler,
        ReaderFactory $readerFactory
    ) {
        $this->readerFactory = $readerFactory;
        $this->importHandler = $importHandler;
    }

    /**
     * @param array $source
     * @return array
     */
    public function import(array $source): array
    {
        /** @var Reader $reader */
        $reader = $this->readerFactory->create(['sourceArray' => $source]);

        return $this->importHandler->execute($reader);
    }
}
