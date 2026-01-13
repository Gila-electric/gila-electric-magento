<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item;

use Amasty\QuickOrder\Model\IsAvailableInterface;
use Amasty\QuickOrder\Model\Item\Manager as ItemManager;
use Amasty\QuickOrder\Model\Item\ProductProvider;
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
     * @var IsAvailableInterface
     */
    private $getIsAvailable;

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
     * @var array
     */
    private $data;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var \Zend\Uri\Uri
     */
    private $zendUri;

    public function __construct(
        ProductProvider $productProvider,
        ItemManager $itemManager,
        IsAvailableInterface $getIsAvailable,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        DesignLoader $designLoader,
        LoggerInterface $logger,
        \Zend\Uri\Uri $zendUri,
        array $data = []
    ) {
        $this->getIsAvailable = $getIsAvailable;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->data = $data;
        $this->logger = $logger;
        $this->productProvider = $productProvider;
        $this->itemManager = $itemManager;
        $this->designLoader = $designLoader;
        $this->zendUri = $zendUri;
    }

    /**
     * @return ResultInterface
     */
    abstract protected function action();

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            if (!$this->getIsAvailable->execute()) {
                return $this->generateResult(Response::STATUS_CODE_403, [
                    'message' => $this->escape(__('Not available.'))
                ]);
            }

            $this->designLoader->load();

            return $this->action();
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
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
     * @return \Zend\Uri\Uri
     */
    public function getZendUri(): \Zend\Uri\Uri
    {
        return $this->zendUri;
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
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

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return ProductProvider
     */
    protected function getProductProvider(): ProductProvider
    {
        return $this->productProvider;
    }

    /**
     * @return ItemManager
     */
    protected function getItemManager(): ItemManager
    {
        return $this->itemManager;
    }
}
