<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block;

use Amasty\QuickOrder\Model\ConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Grid extends Template
{
    /**
     * @var array|LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->configProvider = $configProvider;
    }

    /**
     * @return Grid
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->configProvider->getLabel());

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    public function isComponentExist(string $component): bool
    {
        return isset($this->jsLayout['components'][$component]);
    }
}
