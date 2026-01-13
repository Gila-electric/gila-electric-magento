<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /* ===== General config paths ===== */
    public const XML_PATH_ENABLED   = 'ignitix_blackfriday25/general/enabled';
    public const XML_PATH_START     = 'ignitix_blackfriday25/general/start';
    public const XML_PATH_END       = 'ignitix_blackfriday25/general/end';

    // Prefer min_total; fallback to legacy threshold
    public const XML_PATH_MIN_TOTAL = 'ignitix_blackfriday25/general/min_total';
    public const XML_PATH_THRESHOLD = 'ignitix_blackfriday25/general/threshold';

    // New gates
    public const XML_PATH_HIGH_TIER_MIN_TOTAL   = 'ignitix_blackfriday25/general/high_tier_min_total';
    public const XML_PATH_CAT_TIER_CATEGORY_ID  = 'ignitix_blackfriday25/general/category_tier_category_id';
    public const XML_PATH_CAT_TIER_MIN_SUBTOTAL = 'ignitix_blackfriday25/general/category_tier_min_subtotal';

    /* ===== Prize config paths ===== */
    // Baseline (4 prizes)
    public const XML_PATH_BASELINE = [
        ['sku' => 'ignitix_blackfriday25/prizes_baseline/sku_baseline_1', 'weight' => 'ignitix_blackfriday25/prizes_baseline/weight_baseline_1'],
        ['sku' => 'ignitix_blackfriday25/prizes_baseline/sku_baseline_2', 'weight' => 'ignitix_blackfriday25/prizes_baseline/weight_baseline_2'],
        ['sku' => 'ignitix_blackfriday25/prizes_baseline/sku_baseline_3', 'weight' => 'ignitix_blackfriday25/prizes_baseline/weight_baseline_3'],
        ['sku' => 'ignitix_blackfriday25/prizes_baseline/sku_baseline_4', 'weight' => 'ignitix_blackfriday25/prizes_baseline/weight_baseline_4'],
    ];

    // High-tier (pooled with baseline when cart meets high-tier gate)
    public const XML_PATH_HIGH_SKU     = 'ignitix_blackfriday25/prizes_high/sku_high';
    public const XML_PATH_HIGH_WEIGHT  = 'ignitix_blackfriday25/prizes_high/weight_high';

    // Category-tier (ONLY pool when category subtotal condition met)
    public const XML_PATH_CAT_TIER = [
        ['sku' => 'ignitix_blackfriday25/prizes_category_tier/sku_cat_1', 'weight' => 'ignitix_blackfriday25/prizes_category_tier/weight_cat_1'],
        ['sku' => 'ignitix_blackfriday25/prizes_category_tier/sku_cat_2', 'weight' => 'ignitix_blackfriday25/prizes_category_tier/weight_cat_2'],
        ['sku' => 'ignitix_blackfriday25/prizes_category_tier/sku_cat_3', 'weight' => 'ignitix_blackfriday25/prizes_category_tier/weight_cat_3'],
    ];

    private TimezoneInterface $timezone;
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    private ConfigurableResource $configurableResource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $timezone,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ConfigurableResource $configurableResource
    ) {
        parent::__construct($context);
        $this->timezone             = $timezone;
        $this->productRepository    = $productRepository;
        $this->storeManager         = $storeManager;
        $this->configurableResource = $configurableResource;
    }

    /* ====================== Flags & Scheduling ====================== */

    public function isEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getThreshold(?int $storeId = null): float
    {
        // Prefer new "min_total", fallback to legacy "threshold"
        $val = (float)$this->scopeConfig->getValue(self::XML_PATH_MIN_TOTAL, ScopeInterface::SCOPE_STORE, $storeId);
        if ($val <= 0.0) {
            $val = (float)$this->scopeConfig->getValue(self::XML_PATH_THRESHOLD, ScopeInterface::SCOPE_STORE, $storeId);
        }
        return max(0.0, $val);
    }

    public function getHighTierMinTotal(?int $storeId = null): float
    {
        $val = (float)$this->scopeConfig->getValue(self::XML_PATH_HIGH_TIER_MIN_TOTAL, ScopeInterface::SCOPE_STORE, $storeId);
        return max(0.0, $val ?: 20000.0);
    }

    public function getCategoryTierCategoryId(?int $storeId = null): int
    {
        $val = (int)$this->scopeConfig->getValue(self::XML_PATH_CAT_TIER_CATEGORY_ID, ScopeInterface::SCOPE_STORE, $storeId);
        return $val > 0 ? $val : 508;
    }

    public function getCategoryTierMinSubtotal(?int $storeId = null): float
    {
        $val = (float)$this->scopeConfig->getValue(self::XML_PATH_CAT_TIER_MIN_SUBTOTAL, ScopeInterface::SCOPE_STORE, $storeId);
        return max(0.0, $val ?: 12000.0);
    }

    public function isInDateRange(?int $storeId = null): bool
    {
        $startStr = (string)$this->scopeConfig->getValue(self::XML_PATH_START, ScopeInterface::SCOPE_STORE, $storeId);
        $endStr   = (string)$this->scopeConfig->getValue(self::XML_PATH_END,   ScopeInterface::SCOPE_STORE, $storeId);

        try {
            $tzId = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE, $storeId) ?: 'UTC';
            $tz   = new \DateTimeZone($tzId);
            $now  = new \DateTime('now', $tz);

            $start = new \DateTime($startStr ?: '2025-01-01 00:00:00', $tz);
            $end   = new \DateTime($endStr   ?: '2025-12-31 23:59:59', $tz);

            return ($now >= $start && $now <= $end);
        } catch (\Exception $e) {
            return false;
        }
    }

    /* ====================== Prize Config Readers ====================== */

    /** @return array<int,array{sku:string,weight:int}> */
    public function getBaselinePrizeConfig(?int $storeId = null): array
    {
        $out = [];
        foreach (self::XML_PATH_BASELINE as $row) {
            $sku    = trim((string)$this->scopeConfig->getValue($row['sku'],    ScopeInterface::SCOPE_STORE, $storeId));
            $weight = (int)$this->scopeConfig->getValue($row['weight'], ScopeInterface::SCOPE_STORE, $storeId);
            if ($sku !== '' && $weight > 0) {
                $out[] = ['sku' => $sku, 'weight' => $weight];
            }
        }
        return $out;
    }

    /** @return array<int,array{sku:string,weight:int}> */
    public function getCategoryTierPrizeConfig(?int $storeId = null): array
    {
        $out = [];
        foreach (self::XML_PATH_CAT_TIER as $row) {
            $sku    = trim((string)$this->scopeConfig->getValue($row['sku'],    ScopeInterface::SCOPE_STORE, $storeId));
            $weight = (int)$this->scopeConfig->getValue($row['weight'], ScopeInterface::SCOPE_STORE, $storeId);
            if ($sku !== '' && $weight > 0) {
                $out[] = ['sku' => $sku, 'weight' => $weight];
            }
        }
        return $out;
    }

    public function getHighTierPrize(?int $storeId = null): array
    {
        $sku    = trim((string)$this->scopeConfig->getValue(self::XML_PATH_HIGH_SKU,    ScopeInterface::SCOPE_STORE, $storeId));
        $weight = (int)$this->scopeConfig->getValue(self::XML_PATH_HIGH_WEIGHT, ScopeInterface::SCOPE_STORE, $storeId);
        return ($sku !== '' && $weight > 0) ? ['sku' => $sku, 'weight' => $weight] : [];
    }

    /* ====================== Cart Introspection ====================== */

    /**
     * Sum of row totals for items that belong to given category ID (excludes free gift).
     */
    public function getCategorySubtotal(Quote $quote, int $categoryId): float
    {
        $targetId = (int)$categoryId;
        $sum = 0.0;

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) { continue; }
            if ((int)$item->getData('ignitix_bf25_gift') === 1) { continue; }

            $product = $item->getProduct();
            $catIds  = $product ? (array)$product->getCategoryIds() : [];

            if (!$catIds) {
                try {
                    $product = $this->productRepository->getById((int)$item->getProductId());
                    $catIds  = (array)$product->getCategoryIds();
                } catch (\Throwable $e) {
                    $catIds = [];
                }
            }

            // Normalize to int for strict comparison
            $catIds = array_map('intval', $catIds);

            if (in_array($targetId, $catIds, true)) {
                $sum += (float)$item->getRowTotal(); // tax-excl; adjust if needed
            }
        }

        return $sum;
    }

    /**
     * Subtotal of the cart excluding the free gift line (rowTotal, tax-excl).
     */
    public function getCartSubtotalExclGift(Quote $quote): float
    {
        $sum = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) { continue; }
            if ((int)$item->getData('ignitix_bf25_gift') === 1) { continue; }
            $sum += (float)$item->getRowTotal();
        }
        return $sum;
    }

    /**
     * Eligibility based on settings + date + subtotal excluding gift.
     */
    public function isPromotionApplicableForQuote(Quote $quote, ?int $storeId = null): bool
    {
        if (!$this->isEnabled($storeId))     { return false; }
        if (!$this->isInDateRange($storeId)) { return false; }
        return $this->getCartSubtotalExclGift($quote) >= $this->getThreshold($storeId);
    }

    /* ====================== Prize Pool Builder ====================== */

    /**
     * Build the prize pool given cart context.
     * Priority:
     *  1) Category-tier active ⇒ ONLY the 3 category-tier prizes (weighted).
     *  2) Else baseline 4 prizes; if cart ≥ high-tier gate, include high-tier prize into same pool (weighted).
     *
     * @return array<int,array{sku:string,weight:int}>
     */
    public function buildPrizePool(Quote $quote, ?int $storeId = null): array
    {
        // 1) Category-tier gate
        $catId       = $this->getCategoryTierCategoryId($storeId);
        $catMin      = $this->getCategoryTierMinSubtotal($storeId);
        $catSubtotal = $this->getCategorySubtotal($quote, $catId);
        $catPool     = $this->getCategoryTierPrizeConfig($storeId);

        if ($catSubtotal >= $catMin && !empty($catPool)) {
            // Only use category-tier prizes
            return $catPool;
        }

        // 2) Baseline pool
        $pool = $this->getBaselinePrizeConfig($storeId);

        // Include high-tier with baseline if cart meets high-tier gate (by subtotal excl. gift)
        $highGate = $this->getHighTierMinTotal($storeId);
        if ($this->getCartSubtotalExclGift($quote) >= $highGate) {
            $high = $this->getHighTierPrize($storeId);
            if (!empty($high)) {
                $pool[] = $high;
            }
        }

        return $pool;
    }

    /* ====================== Draw Logic ====================== */

    /**
     * Draw a prize product from the pool (prefilter to salable simple/virtual or salable child of configurable).
     *
     * @throws NoSuchEntityException
     */
    public function drawPrizeProduct(?Quote $quote = null, ?int $storeId = null): ProductInterface
    {
        if ($quote === null) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            try {
                /** @var \Magento\Checkout\Model\Session $sess */
                $sess  = $om->get(\Magento\Checkout\Model\Session::class);
                $quote = $sess->getQuote();
            } catch (\Throwable $e) {
                $quote = null;
            }
        }
        if (!$quote || !$quote->getId()) {
            throw new NoSuchEntityException(__('No active cart.'));
        }

        // Build intended pool from config/conditions
        $rawPool = $this->buildPrizePool($quote, $storeId);
        if (!$rawPool) {
            throw new NoSuchEntityException(__('No valid prizes configured.'));
        }

        // Resolve effective store id (prefer quote's store, then injected, then current)
        $effectiveStoreId = $storeId ?? (int)$quote->getStoreId();
        if ($effectiveStoreId <= 0) {
            $effectiveStoreId = (int)$this->storeManager->getStore()->getId();
        }

        // Prefilter to salable prizes only (ignore OOS => their weight is effectively 0)
        $filtered = [];
        $sum = 0;

        foreach ($rawPool as $entry) {
            $sku    = trim($entry['sku'] ?? '');
            $weight = (int)($entry['weight'] ?? 0);
            if ($sku === '' || $weight <= 0) {
                continue;
            }

            try {
                $product = $this->productRepository->get($sku, false, $effectiveStoreId, true);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $chosen = null;
            $type   = (string)$product->getTypeId();

            if ($type === 'configurable') {
                // Find first salable child
                $children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($children as $child) {
                    if ($child->isSalable()) {
                        $chosen = $this->productRepository->get($child->getSku(), false, $effectiveStoreId, true);
                        break;
                    }
                }
            } elseif ($type === 'simple' || $type === 'virtual') {
                if ($product->isSalable()) {
                    $chosen = $product;
                }
            }

            if ($chosen) {
                $filtered[] = ['product' => $chosen, 'weight' => $weight];
                $sum       += $weight;
            }
        }

        if ($sum <= 0 || !$filtered) {
            throw new NoSuchEntityException(__('No salable prize configured.'));
        }

        // Weighted random among salable prizes
        $roll = random_int(1, $sum);
        $acc  = 0;
        foreach ($filtered as $entry) {
            $acc += $entry['weight'];
            if ($roll <= $acc) {
                return $entry['product'];
            }
        }

        // Fallback (should not happen)
        return $filtered[0]['product'];
    }

    /**
     * Legacy grand-total based check (kept for backward compatibility).
     * Prefer isPromotionApplicableForQuote().
     */
    public function isPromotionApplicable(float $grandTotal, ?int $storeId = null): bool
    {
        if (!$this->isEnabled($storeId))     { return false; }
        if (!$this->isInDateRange($storeId)) { return false; }
        return $grandTotal >= $this->getThreshold($storeId);
    }

    /** Build a stable hash of non-gift visible items (sku:qty sorted). */
    public function getItemsHash(Quote $quote): string
    {
        $parts = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) { continue; }
            if ((int)$item->getData('ignitix_bf25_gift') === 1) { continue; }
            $parts[] = $item->getSku() . ':' . (float)$item->getQty();
        }
        sort($parts, SORT_STRING);
        return md5(implode('|', $parts));
    }
}