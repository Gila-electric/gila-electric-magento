<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Block\Product;

use Ignitix\ProductAddons\Model\RuleProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Addons extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Registry $registry,
        private readonly RuleProvider $ruleProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getProduct(): ?ProductInterface
    {
        $p = $this->registry->registry('current_product');
        return $p instanceof ProductInterface ? $p : null;
    }

    public function getAddons(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }
        return $this->ruleProvider->getAddonProductsFor($product);
    }
}