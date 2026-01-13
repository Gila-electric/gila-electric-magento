<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Grid;

use Amasty\QuickOrder\Model\ConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class GridConfigProcessor implements LayoutProcessorInterface
{
    public const REMOVE_URL = 'amasty_quickorder/item/remove';
    public const UPDATE_URL = 'amasty_quickorder/item/update';
    public const CLEAR_URL = 'amasty_quickorder/item/removeAll';
    public const VALIDATE_URL = 'amasty_quickorder/item/getValidateData';
    public const GET_ALL_URL = 'amasty_quickorder/item/getAll';
    public const EXPORT_URL = 'amasty_quickorder/file_export/grid';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
    }

    public function process($jsLayout): array
    {
        if (isset($jsLayout['components']['grid']['config'])) {
            $jsLayout['components']['grid']['config']['storeCheck']
                = $this->storeManager->getStore()->getCurrentCurrencyCode() . $this->storeManager->getStore()->getId();
            $jsLayout['components']['grid']['config']['removeUrl'] = $this->getUrl(static::REMOVE_URL);
            $jsLayout['components']['grid']['config']['updateUrl'] = $this->getUrl(static::UPDATE_URL);
            $jsLayout['components']['grid']['config']['clearUrl'] = $this->getUrl(static::CLEAR_URL);
            $jsLayout['components']['grid']['config']['validateUrl'] = $this->getUrl(static::VALIDATE_URL);
            $jsLayout['components']['grid']['config']['getAllUrl'] = $this->getUrl(static::GET_ALL_URL);
            $jsLayout['components']['grid']['config']['exportUrl'] = $this->getUrl(static::EXPORT_URL);
            $jsLayout['components']['grid']['config']['exportEnabled'] = $this->configProvider->isDownloadListAllowed();
        }

        return $jsLayout;
    }

    private function getUrl(string $route): string
    {
        return $this->urlBuilder->getUrl($route);
    }
}
