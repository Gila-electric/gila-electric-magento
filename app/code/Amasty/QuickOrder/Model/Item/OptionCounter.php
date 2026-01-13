<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class OptionCounter
{
    /**
     * @param Product $product
     *
     * @return int
     */
    public function get(Product $product)
    {
        $count = 0;
        $options = $product->getOptions();
        if (is_array($options)) {
            $count = count($options);
        }

        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                $options = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
                $count += is_array($options) ? count($options) : 0;
                break;
            case Type::TYPE_DOWNLOADABLE:
                $count++;
                break;
            case 'giftcard':
                $count += 3;//more then 2
                break;
            case BundleType::TYPE_CODE:
                $options = $product->getTypeInstance()->getOptionsIds($product);
                $count += is_array($options) ? count($options) : 0;
                break;
            case Grouped::TYPE_CODE:
                $count += count($product->getTypeInstance()->getAssociatedProducts($product));
                break;
        }

        return $count;
    }
}
