<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Grid;

use Amasty\QuickOrder\Model\ConfigProvider;

class PagerConfigProcessor implements LayoutProcessorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function process($jsLayout): array
    {
        if (isset($jsLayout['components']['pager']['config'])) {
            $jsLayout['components']['pager']['config']['pageSize'] = $this->configProvider->getPageSize();
        }

        return $jsLayout;
    }
}
