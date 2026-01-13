<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\File\Sample;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ActionInterface as ActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Dir\Reader;

abstract class AbstractAction implements ActionInterface
{
    public const FILE_NAME = 'sample';
    public const FILE_FORMAT = 'csv';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    public function __construct(
        Reader $reader,
        ReadFactory $readFactory,
        FileFactory $fileFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->fileFactory = $fileFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
    }

    /**
     * @return Redirect|Raw
     */
    public function execute()
    {
        $moduleDir = $this->reader->getModuleDir('', 'Amasty_QuickOrder');
        $fileName = sprintf('%s.%s', static::FILE_NAME, static::FILE_FORMAT);
        $fileAbsolutePath = sprintf('%s/Files/Sample/%s', $moduleDir, $fileName);

        try {
            $directoryRead = $this->readFactory->create($moduleDir);
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

            if (!$directoryRead->isFile($filePath)) {
                return $this->createRedirect();
            }

            $this->fileFactory->create(
                $fileName,
                null,
                DirectoryList::VAR_DIR,
                'application/octet-stream',
                $directoryRead->stat($filePath)['size']
            );

            /** @var Raw $resultRaw */
            $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $result->setContents($directoryRead->readFile($filePath));
        } catch (Exception $e) {
            $result = $this->createRedirect();
        }

        return $result;
    }

    /**
     * @return Redirect
     */
    private function createRedirect(): Redirect
    {
        $this->messageManager->addErrorMessage(__('Some problems with downloading the sample file.'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
