<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CartAddProductPlugin
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function aroundAddProduct(Cart $subject, callable $proceed, $productInfo, $requestInfo = null)
    {
        $request = $requestInfo instanceof DataObject
            ? $requestInfo
            : new DataObject(is_array($requestInfo) ? $requestInfo : []);

        // If we're adding an addon product internally, don't re-add addons.
        if ((int)$request->getData('ignitix_is_addon') === 1) {
            return $proceed($productInfo, $requestInfo);
        }

        $addonSkus = $request->getData('ignitix_addon_skus');
        if (!is_array($addonSkus) || !$addonSkus) {
            return $proceed($productInfo, $requestInfo);
        }

        // Add main product first
        $parentItem = $proceed($productInfo, $requestInfo);

        if (!$parentItem instanceof QuoteItem) {
            return $parentItem;
        }

        $qty = (float)($parentItem->getQty() ?: (float)$request->getData('qty') ?: 1.0);
        $parentSku = (string)$parentItem->getSku();

        // Group id ties parent + addons together (no dependency on quote item DB id)
        $groupId = bin2hex(random_bytes(8));

        $parentItem->addOption([
            'code'  => 'ignitix_addon_group_id',
            'value' => $groupId,
        ]);

        foreach ($addonSkus as $sku) {
            $sku = trim((string)$sku);
            if ($sku === '') {
                continue;
            }

            try {
                $addonProduct = $this->productRepository->get($sku);
            } catch (NoSuchEntityException) {
                continue;
            }

            if (!$addonProduct->isSaleable()) {
                continue;
            }

            $addonRequest = new DataObject([
                'qty' => $qty,
                'ignitix_is_addon' => 1,
            ]);

            $addonItem = $subject->addProduct($addonProduct, $addonRequest);

            if ($addonItem instanceof QuoteItem) {
                $addonItem->addOption(['code' => 'ignitix_is_addon', 'value' => '1']);
                $addonItem->addOption(['code' => 'ignitix_addon_group_id', 'value' => $groupId]);
                $addonItem->addOption(['code' => 'ignitix_parent_sku', 'value' => $parentSku]);

                $this->mergeAdditionalOptions($addonItem, [
                    ['label' => 'Add-on for', 'value' => $parentSku],
                ]);
            }
        }

        return $parentItem;
    }

    private function mergeAdditionalOptions(QuoteItem $item, array $toAdd): void
    {
        $option = $item->getOptionByCode('additional_options');
        $existing = [];

        if ($option && $option->getValue()) {
            $decoded = @unserialize($option->getValue());
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $merged = array_merge($existing, $toAdd);

        if ($option) {
            $option->setValue(serialize($merged));
        } else {
            $item->addOption([
                'code'  => 'additional_options',
                'value' => serialize($merged),
            ]);
        }
    }
}