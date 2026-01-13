<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller;

use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Model\GetIsAvailable;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    public const TARGET_MODULE_NAME = 'amasty_quickorder';
    public const TARGET_CONTROLLER_NAME = 'index';
    public const TARGET_ACTION_NAME = 'index';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var GetIsAvailable
     */
    private $getIsAvailable;

    public function __construct(
        GetIsAvailable $getIsAvailable,
        ConfigProvider $configProvider,
        ActionFactory $actionFactory
    ) {
        $this->configProvider = $configProvider;
        $this->actionFactory = $actionFactory;
        $this->getIsAvailable = $getIsAvailable;
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|void
     */
    public function match(RequestInterface $request)
    {
        $urlKey = $this->prepareUrl($request->getPathInfo());
        $quickOrderUrl = $this->prepareUrl($this->configProvider->getUrlKey());

        if ($quickOrderUrl === $urlKey && $this->getIsAvailable->execute()) {
            $this->updateRequestInfo($request, $urlKey);
            return $this->createForward();
        }
    }

    /**
     * @param string $url
     * @return string
     */
    private function prepareUrl(string $url): string
    {
        return trim($url, '/');
    }

    /**
     * @param RequestInterface $request
     * @param string $urlKey
     */
    private function updateRequestInfo(RequestInterface $request, string $urlKey)
    {
        $request->setModuleName(static::TARGET_MODULE_NAME);
        $request->setControllerName(static::TARGET_CONTROLLER_NAME);
        $request->setActionName(static::TARGET_ACTION_NAME);
        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
    }

    /**
     * @return ActionInterface
     */
    private function createForward(): ActionInterface
    {
        return $this->actionFactory->create(Forward::class);
    }
}
