<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\File\Export;

use Amasty\QuickOrder\Model\Export\HandlerFactory;
use Exception;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class Grid implements ActionInterface
{
    public const FILE_TYPE_PARAM = 'type';
    public const FILE_NAME = 'export';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var HandlerFactory
     */
    private $handlerFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HandlerFactory $handlerFactory,
        RequestInterface $request,
        FileFactory $fileFactory,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        RedirectInterface $redirect,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->handlerFactory = $handlerFactory;
        $this->fileFactory = $fileFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $fileFormat = $this->getType();

        try {
            return $this->fileFactory->create(
                sprintf('%s.%s', static::FILE_NAME, $fileFormat),
                $this->handlerFactory->create($fileFormat)->getFile(),
                DirectoryList::VAR_DIR
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong. Please review the error log.'));
            return $this->createRedirect();
        }
    }

    protected function getType(): string
    {
        return $this->request->getParam(static::FILE_TYPE_PARAM, 'csv');
    }

    private function createRedirect(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
