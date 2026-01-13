<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Category\Grid;

use Amasty\QuickOrder\Block\Grid\LayoutProcessorInterface;
use Magento\Framework\UrlInterface;

class GridConfigProcessor implements LayoutProcessorInterface
{
    public const UPDATE_URL = 'amasty_quickorder/category/updateItem';
    public const OPTIONS_URL = 'amasty_quickorder/category/getOptions';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function process($jsLayout): array
    {
        if (isset($jsLayout['components']['grid']['config'])) {
            $jsLayout['components']['grid']['config']['updateUrl'] = $this->getUrl(static::UPDATE_URL);
            $jsLayout['components']['grid']['config']['loadOptionsUrl'] = $this->getUrl(static::OPTIONS_URL);
        }

        return $jsLayout;
    }

    private function getUrl(string $route): string
    {
        return $this->urlBuilder->getUrl($route);
    }
}
