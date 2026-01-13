<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Model;

use Ignitix\ProductAddons\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class RuleProvider
{
    public function __construct(
        private readonly RuleCollectionFactory $ruleCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {}

    /**
     * @return array<int, array{sku:string,name:string,price:float,price_formatted:string}>
     */
    public function getAddonProductsFor(ProductInterface $product): array
    {
        $productId = (int)$product->getId();
        $productSku = (string)$product->getSku();
        $categoryIds = array_map('intval', (array)$product->getCategoryIds());

        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('sort_order', 'ASC');
        $collection->setOrder('rule_id', 'ASC');

        $seen = [];
        $result = [];

        foreach ($collection as $rule) {
            $targetsIds = $this->csvToInts((string)$rule->getData('target_product_ids'));
            $targetsSkus = $this->csvToStrings((string)$rule->getData('target_product_skus'));
            $targetsCats = $this->csvToInts((string)$rule->getData('target_category_ids'));

            // if no targets defined, skip
            if (!$targetsIds && !$targetsSkus && !$targetsCats) {
                continue;
            }

            $applies =
                ($targetsIds && in_array($productId, $targetsIds, true)) ||
                ($targetsSkus && in_array($productSku, $targetsSkus, true)) ||
                ($targetsCats && $categoryIds && count(array_intersect($targetsCats, $categoryIds)) > 0);

            if (!$applies) {
                continue;
            }

            $addonSku = trim((string)$rule->getData('addon_sku'));
            if ($addonSku === '' || isset($seen[$addonSku])) {
                continue;
            }

            try {
                $addon = $this->productRepository->get($addonSku);
            } catch (NoSuchEntityException) {
                continue;
            }

            if (!$addon->isSaleable()) {
                continue;
            }

            $price = (float)$addon->getFinalPrice();
            $seen[$addonSku] = true;

            $result[] = [
                'sku' => (string)$addon->getSku(),
                'name' => (string)$addon->getName(),
                'price' => $price,
                'price_formatted' => $this->priceCurrency->convertAndFormat($price),
            ];
        }

        return $result;
    }

    /** @return int[] */
    private function csvToInts(string $csv): array
    {
        $csv = trim($csv);
        if ($csv === '') return [];
        $parts = array_map('trim', explode(',', $csv));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            if (!ctype_digit($p)) continue;
            $out[] = (int)$p;
        }
        return array_values(array_unique($out));
    }

    /** @return string[] */
    private function csvToStrings(string $csv): array
    {
        $csv = trim($csv);
        if ($csv === '') return [];
        $parts = array_map('trim', explode(',', $csv));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $out[] = $p;
        }
        $out = array_values(array_unique($out));
        return $out;
    }
}