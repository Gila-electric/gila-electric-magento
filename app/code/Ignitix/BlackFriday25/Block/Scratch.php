<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Block;

use Magento\Framework\View\Element\Template;
use Ignitix\BlackFriday25\Helper\Data as Helper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class Scratch extends Template
{
    private Helper $helper;
    private CheckoutSession $checkoutSession;
    private ProductRepositoryInterface $productRepository;
    private ImageHelper $imageHelper;

    public function __construct(
        Template\Context $context,
        Helper $helper,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper            = $helper;
        $this->checkoutSession   = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->imageHelper       = $imageHelper;
    }

    public function isEnabled(): bool
    {
        return $this->helper->isEnabled();
    }

    public function isInDateRange(): bool
    {
        return $this->helper->isInDateRange();
    }

    /** Legacy accessor still used by some JS */
    public function getThreshold(): float
    {
        return $this->helper->getThreshold();
    }

    /** Preferred; safe fallback if helper doesn’t expose getMinTotal() */
    public function getMinTotal(): float
    {
        return \method_exists($this->helper, 'getMinTotal')
            ? (float)$this->helper->getMinTotal()
            : (float)$this->helper->getThreshold();
    }

    public function getGrandTotal(): float
    {
        $q = $this->checkoutSession->getQuote();
        return $q ? (float)$q->getGrandTotal() : 0.0;
    }

    public function getApplyUrl(): string
    {
        return $this->getUrl('ignitix_bf25/prize/apply');
    }

    public function getStatusUrl(): string
    {
        return $this->getUrl('ignitix_bf25/prize/status');
    }

    /** New: for locking the visual prize image before reveal */
    public function getPreselectUrl(): string
    {
        return $this->getUrl('ignitix_bf25/prize/preselect');
    }

    public function isApplied(): bool
    {
        $q = $this->checkoutSession->getQuote();
        return $q ? (bool)$q->getData('ignitix_bf25_applied') : false;
    }

    /**
     * Read configured prize SKUs:
     * - Base (sku1..sku5)
     * - High-tier (sku6)
     * - Category-508 tier (sku508a, sku508b, sku508c)
     *
     * @return string[]
     */
    private function getPrizeSkus(): array
    {
        $paths = [
            'ignitix_blackfriday25/prizes/sku1',
            'ignitix_blackfriday25/prizes/sku2',
            'ignitix_blackfriday25/prizes/sku3',
            'ignitix_blackfriday25/prizes/sku4',
            'ignitix_blackfriday25/prizes/sku5',
            'ignitix_blackfriday25/prizes/sku6',
            // Category 508 tier (3 prizes)
            'ignitix_blackfriday25/prizes/cat508/sku_a',
            'ignitix_blackfriday25/prizes/cat508/sku_b',
            'ignitix_blackfriday25/prizes/cat508/sku_c',
        ];

        $out = [];
        foreach ($paths as $p) {
            $sku = trim((string)$this->_scopeConfig->getValue($p));
            if ($sku !== '' && !in_array($sku, $out, true)) {
                $out[] = $sku;
            }
        }
        return $out;
    }

    /**
     * Build 100x100 image URLs for all configured prizes (used by legacy grid UIs).
     * Current scratch flow injects only the single preselected image, but this remains
     * for backward compatibility and admin previews.
     *
     * @return array<int,array{url:string,alt:string}>
     */
    public function getPrizeImageUrls(): array
    {
        $urls = [];
        $storeId = (int)$this->_storeManager->getStore()->getId();

        foreach ($this->getPrizeSkus() as $sku) {
            try {
                $product = $this->productRepository->get($sku, false, $storeId, true);
                $urls[] = [
                    'url' => $this->imageHelper
                        ->init($product, 'product_small_image')
                        ->resize(100, 100)
                        ->getUrl(),
                    'alt' => (string)$product->getName(),
                ];
            } catch (\Throwable $e) {
                // skip missing/inaccessible products
            }
        }
        return $urls;
    }
}