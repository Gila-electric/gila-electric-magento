<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Catalog\Block\Product\View\Options;

use Amasty\QuickOrder\Model\Item\Block\Renderer;
use Amasty\QuickOrder\Model\Item\Block\Renderer\Flag as RendererFlag;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Framework\View\LayoutInterface;

class AbstractOptionsPlugin
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var RendererFlag
     */
    private $rendererFlag;

    public function __construct(
        Renderer $renderer,
        RendererFlag $rendererFlag
    ) {
        $this->renderer = $renderer;
        $this->rendererFlag = $rendererFlag;
    }

    /**
     * Need replace layout for custom options, when shared layout not contain catalog_product_view handle
     * Need add catalog_product_view handle for avoid
     * errors with product.price.render.default in \Magento\Catalog\Block\Product\View\Options\AbstractOptions.
     * Errors caused for select options \Magento\Catalog\Block\Product\View\Options\Type\Select,
     * because multipleFactory and checkableFactory uses shared layout from template context instead of current layout
     *
     * @param AbstractOptions $abstractOptions
     * @param LayoutInterface $layout
     * @return LayoutInterface
     */
    public function afterGetLayout(AbstractOptions $abstractOptions, LayoutInterface $layout): LayoutInterface
    {
        if ($this->rendererFlag->isActive()) {
            $layout = $this->renderer->getLayout($abstractOptions->getProduct()->getTypeId());
        }

        return $layout;
    }
}
