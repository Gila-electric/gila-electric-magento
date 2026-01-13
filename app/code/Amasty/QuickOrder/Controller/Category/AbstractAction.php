<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Category;

use Amasty\QuickOrder\Model\CategoryMode;
use Exception;
use Magento\Framework\App\ActionInterface as ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Phrase;
use Magento\Framework\View\DesignLoader;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zend\Http\AbstractMessage;
use Zend\Http\Response;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var CategoryMode
     */
    private $categoryMode;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        CategoryMode $categoryMode,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        DesignLoader $designLoader,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->designLoader = $designLoader;
        $this->logger = $logger;
        $this->data = $data;
        $this->categoryMode = $categoryMode;
    }

    abstract protected function action(): ResultInterface;

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            if (!$this->categoryMode->isAvailable()) {
                return $this->generateResult(Response::STATUS_CODE_403, [
                    'message' => $this->escape(__('Not available.'))
                ]);
            }

            $this->designLoader->load();

            return $this->action();
        } catch (Exception $ex) {
            $this->getLogger()->error($ex->getMessage());
            return $this->generateResult(Response::STATUS_CODE_200, [
                'message' => $this->escape(__('Something went wrong. Please review error log.'))
            ]);
        }
    }

    /**
     * @param int $code
     * @param array $data
     * @return Json
     */
    protected function generateResult($code, $data): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setStatusHeader(
            $code,
            AbstractMessage::VERSION_11
        );
        return $resultJson->setData($data);
    }

    /**
     * @param Phrase|string $message
     * @return string
     */
    protected function escape($message): string
    {
        return $this->escaper->escapeHtml($message);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RuntimeException
     */
    protected function getData(string $key)
    {
        if (!isset($this->data[$key])) {
            throw new RuntimeException(sprintf('%s not provided for %s', $key, static::class));
        }

        return $this->data[$key];
    }
}
