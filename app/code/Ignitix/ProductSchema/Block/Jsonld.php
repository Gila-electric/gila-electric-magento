<?php
namespace Ignitix\ProductSchema\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class Jsonld extends Template
{
    protected $_registry;
    protected $_storeManager;
    protected $_imageHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getJsonLd()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return null;
        }

        $imageUrl = $this->_imageHelper->init($product, 'product_base_image')->getUrl();
        $productUrl = $product->getProductUrl();
        $price = $product->getFinalPrice();
        $availability = $product->isAvailable() ? "http://schema.org/InStock" : "http://schema.org/OutOfStock";

        $description = $product->getShortDescription() ?: $product->getDescription();
        $description = preg_replace('/<br\s*\/?>/i', "\n", $description);
        $description = strip_tags($description);
        $description = trim($description);

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $product->getName(),
            "description" => $description,
            "sku" => $product->getSku(),
            "image" => $imageUrl,
            "url" => $productUrl,
            "brand" => [
                "@type" => "Brand",
                "name" => "Gila Electric"
            ],
            "offers" => [
                "@type" => "Offer",
                "priceCurrency" => "EGP",
                "price" => number_format((float)$price, 2, '.', ''),
                "availability" => $availability,
                "itemCondition" => "http://schema.org/NewCondition",
                "url" => $productUrl
            ]
        ];

        return $schema;
    }
}
