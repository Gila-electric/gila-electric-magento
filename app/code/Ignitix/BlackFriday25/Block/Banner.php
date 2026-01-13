<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Ignitix\BlackFriday25\Helper\Data as Helper;

class Banner extends Template
{
    private CheckoutSession $checkoutSession;
    private ProductRepositoryInterface $productRepository;
    private ImageHelper $imageHelper;
    private Helper $helper;

    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession   = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->imageHelper       = $imageHelper;
        $this->helper            = $helper;
    }

    private function cfg(string $path, $default = null)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE) ?? $default;
    }

    /** Prefer helper’s min_total with legacy fallback. */
    public function getThreshold(): float
    {
        return $this->helper->getThreshold();
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getGrandTotal(): float
    {
        $q = $this->getQuote();
        return $q ? (float)$q->getGrandTotal() : 0.0;
    }

    /** Subtotal excluding the gift (fallback to grand total if helper doesn’t provide it). */
    public function getSubtotalExclGift(): float
    {
        $q = $this->getQuote();
        if ($q && \method_exists($this->helper, 'getCartSubtotalExclGift')) {
            return (float)$this->helper->getCartSubtotalExclGift($q);
        }
        // Legacy fallback
        return $this->getGrandTotal();
    }

    /** Use helper’s quote-aware eligibility so shipping/payment changes don’t drop the gift. */
    public function isEligible(): bool
    {
        $q = $this->getQuote();
        if ($q && \method_exists($this->helper, 'isPromotionApplicableForQuote')) {
            return $this->helper->isPromotionApplicableForQuote($q);
        }
        // Legacy fallback
        return $this->helper->isPromotionApplicable($this->getGrandTotal());
    }

    /** Remaining amount based on subtotal-excl-gift. */
    public function getRemaining(): float
    {
        $delta = $this->getThreshold() - $this->getSubtotalExclGift();
        return $delta > 0 ? $delta : 0.0;
    }

    /**
     * Collect prize SKUs.
     * Preferred (8 total):
     *  - ignitix_blackfriday25/prizes_baseline/sku_baseline_1..4
     *  - ignitix_blackfriday25/prizes_high/sku_high
     *  - ignitix_blackfriday25/prizes_category_tier/sku_cat_1..3
     *
     * Fallbacks for backward compatibility:
     *  - ignitix_blackfriday25/prizes/normal_1_sku..normal_4_sku
     *  - ignitix_blackfriday25/prizes/high_sku
     *  - ignitix_blackfriday25/prizes/cat508_1_sku..cat508_3_sku
     *  - ignitix_blackfriday25/prizes/sku1..sku7
     *  - ignitix_blackfriday25/prize/sku1..sku7
     */
    public function getPrizeSkus(): array
    {
        $collect = function (array $paths): array {
            $out = [];
            foreach ($paths as $p) {
                $sku = trim((string)$this->cfg($p, ''));
                if ($sku !== '' && !in_array($sku, $out, true)) {
                    $out[] = $sku;
                }
            }
            return $out;
        };

        // Preferred: new grouped keys (8)
        $preferredPaths = [
            // Baseline (4)
            'ignitix_blackfriday25/prizes_baseline/sku_baseline_1',
            'ignitix_blackfriday25/prizes_baseline/sku_baseline_2',
            'ignitix_blackfriday25/prizes_baseline/sku_baseline_3',
            'ignitix_blackfriday25/prizes_baseline/sku_baseline_4',
            // High-tier (1)
            'ignitix_blackfriday25/prizes_high/sku_high',
            // Category-tier (3)
            'ignitix_blackfriday25/prizes_category_tier/sku_cat_1',
            'ignitix_blackfriday25/prizes_category_tier/sku_cat_2',
            'ignitix_blackfriday25/prizes_category_tier/sku_cat_3',
        ];
        $skus = $collect($preferredPaths);
        if ($skus) {
            return $skus;
        }

        // Fallback: intermediate naming previously used (8)
        $intermediatePaths = [
            'ignitix_blackfriday25/prizes/normal_1_sku',
            'ignitix_blackfriday25/prizes/normal_2_sku',
            'ignitix_blackfriday25/prizes/normal_3_sku',
            'ignitix_blackfriday25/prizes/normal_4_sku',
            'ignitix_blackfriday25/prizes/high_sku',
            'ignitix_blackfriday25/prizes/cat508_1_sku',
            'ignitix_blackfriday25/prizes/cat508_2_sku',
            'ignitix_blackfriday25/prizes/cat508_3_sku',
        ];
        $skus = $collect($intermediatePaths);
        if ($skus) {
            return $skus;
        }

        // Legacy (7)
        $legacy1 = [];
        for ($i = 1; $i <= 7; $i++) {
            $legacy1[] = "ignitix_blackfriday25/prizes/sku{$i}";
        }
        $skus = $collect($legacy1);
        if ($skus) {
            return $skus;
        }

        // Very old (7)
        $legacy2 = [];
        for ($i = 1; $i <= 7; $i++) {
            $legacy2[] = "ignitix_blackfriday25/prize/sku{$i}";
        }
        return $collect($legacy2);
    }

    public function getWonPrizeItem(): ?\Magento\Quote\Model\Quote\Item
    {
        $quote = $this->getQuote();
        if (!$quote) return null;

        $prizeSkus = array_map('strtoupper', $this->getPrizeSkus());

        foreach ($quote->getAllVisibleItems() as $item) {
            $opt = $item->getOptionByCode('bf25_prize') ?: $item->getOptionByCode('ignitix_bf25_prize');
            if ($opt) return $item;

            $skuMatch = in_array(strtoupper((string)$item->getSku()), $prizeSkus, true);
            if ($skuMatch && (float)$item->getPrice() == 0.0) {
                return $item;
            }
        }
        return null;
    }

    public function getPrizeProducts(): array
    {
        $products = [];
        $storeId = (int)$this->_storeManager->getStore()->getId();
        foreach ($this->getPrizeSkus() as $sku) {
            try {
                // store-aware load for correctness
                $products[] = $this->productRepository->get($sku, false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                // ignore missing sku
            }
        }
        return $products;
    }

    public function getImageUrl(Product $product): string
    {
        return $this->imageHelper
            ->init($product, 'product_small_image')
            ->resize(100, 100)
            ->getUrl();
    }

    /**
     * Return JSON safe for embedding directly in an HTML attribute without extra escaping.
     * In your template: data-config='<?= $block->getCheckoutConfigJson() ?>'
     */
    public function getCheckoutConfigJson(): string
    {
        $data = [
            'eligible'     => $this->isEligible(),
            'threshold'    => $this->getThreshold(),
            'grandTotal'   => $this->getGrandTotal(),       // kept for backward compat
            'remaining'    => $this->getRemaining(),        // now based on subtotal-excl-gift
            'openButtonId' => 'bf25-open-gift',
        ];

        return json_encode(
            $data,
            JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ) ?: '{}';
    }

    public function getCurrencySymbol(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }
}