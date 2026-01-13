<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Layout as NativeLayout;

class Layout extends NativeLayout
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true)
    {
        return parent::renderElement($name, false);
    }

    /**
     * Gets HTML of block element
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _renderBlock($name)
    {
        $block = $this->getBlock($name);
        if ($block && $this->getProduct()) {
            /* fix bug with saved product*/
            $block->setProduct($this->getProduct());
            $block->unsetData('allow_products');
        }

        return $block ? $block->toHtml() : '';
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }
}
