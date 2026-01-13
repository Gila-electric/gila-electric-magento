<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Controller\Router;

class UrlModifier
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function modify(string $url): string
    {
        return str_replace(Router::AMASTY_COMPANY_ROUTE, $this->configProvider->getUrlKey(), $url);
    }
}
