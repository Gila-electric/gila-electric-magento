<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Script;

use Magento\Catalog\Block\Product\View;
use Magento\Framework\View\Element\Template;

class Price extends Template implements ConfigInterface
{
    public const PRODUCT_INFO_BLOCK = 'product.info';

    /**
     * @var string
     */
    protected $_template = 'Amasty_QuickOrder::script/price_box.phtml';

    public function getJsonConfig(): string
    {
        /** @var View $block */
        if ($block = $this->getLayout()->getBlock(static::PRODUCT_INFO_BLOCK)) {
            $jsonConfig = $block->getJsonConfig();
        } else {
            $jsonConfig = '{}';
        }

        return $jsonConfig;
    }

    public function getItemId(): int
    {
        return (int) $this->getData('item_id');
    }

    public function setItemId(int $itemId): void
    {
        $this->setData('item_id', $itemId);
    }
}
