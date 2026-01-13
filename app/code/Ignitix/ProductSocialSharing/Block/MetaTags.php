<?php
namespace Ignitix\ProductSocialSharing\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;

class MetaTags extends Template
{
    protected $_registry;
    protected $_imageHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getDescription()
    {
        $product = $this->getProduct();
        $desc = $product->getShortDescription() ?: $product->getDescription();

        $desc = preg_replace('/<br\\s*\\/?>/i', "\n", $desc);

        $desc = strip_tags($desc);

        return trim($desc);
    }

    public function getImageUrl()
    {
        $product = $this->getProduct();
        return $this->_imageHelper->init($product, 'product_base_image')->getUrl();
    }

    public function getProductUrl()
    {
        return $this->getProduct()->getProductUrl();
    }
}
