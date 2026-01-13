<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class QuoteRemoveItemPlugin
{
    private bool $processing = false;

    public function aroundRemoveItem(Quote $subject, callable $proceed, $itemId)
    {
        if ($this->processing) {
            return $proceed($itemId);
        }

        $this->processing = true;
        try {
            $item = $subject->getItemById((int)$itemId);
            if (!$item) {
                return $proceed($itemId);
            }

            $groupId = $this->getOptionValue($item, 'ignitix_addon_group_id');
            $isAddon = $this->isAddon($item);

            $result = $proceed($itemId);

            // removing parent => remove addons in same group
            if (!$isAddon && $groupId) {
                foreach ($subject->getAllItems() as $other) {
                    if (!$other instanceof QuoteItem) continue;
                    if (!$this->isAddon($other)) continue;
                    if ($this->getOptionValue($other, 'ignitix_addon_group_id') !== $groupId) continue;
                    if ($other->getId()) {
                        $subject->removeItem((int)$other->getId());
                    }
                }
            }

            return $result;
        } finally {
            $this->processing = false;
        }
    }

    private function isAddon(QuoteItem $item): bool
    {
        return $this->getOptionValue($item, 'ignitix_is_addon') === '1';
    }

    private function getOptionValue(QuoteItem $item, string $code): ?string
    {
        $opt = $item->getOptionByCode($code);
        return $opt ? (string)$opt->getValue() : null;
    }
}