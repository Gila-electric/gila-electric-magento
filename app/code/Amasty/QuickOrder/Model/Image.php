<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;

class Image
{
    public const IMAGE_ID = 'quickorder_product_grid_image';

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(ImageHelper $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    public function init(Product $product): ImageHelper
    {
        return $this->imageHelper->init($product, static::IMAGE_ID);
    }
}
