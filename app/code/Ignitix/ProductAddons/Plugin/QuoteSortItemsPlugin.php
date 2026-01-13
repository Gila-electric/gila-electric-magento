<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class QuoteSortItemsPlugin
{
    public function afterGetAllVisibleItems(Quote $subject, array $items): array
    {
        $addonsByGroup = [];
        $parentsByGroup = [];

        foreach ($items as $it) {
            if (!$it instanceof QuoteItem) continue;
            $groupId = $this->getOptionValue($it, 'ignitix_addon_group_id');
            $isAddon = $this->getOptionValue($it, 'ignitix_is_addon') === '1';

            if ($groupId) {
                if ($isAddon) {
                    $addonsByGroup[$groupId][] = $it;
                } else {
                    $parentsByGroup[$groupId] = $it;
                }
            }
        }

        $out = [];
        $already = [];

        foreach ($items as $it) {
            if (!$it instanceof QuoteItem) continue;

            $groupId = $this->getOptionValue($it, 'ignitix_addon_group_id');
            $isAddon = $this->getOptionValue($it, 'ignitix_is_addon') === '1';

            if (!$groupId) {
                $out[] = $it;
                $already[spl_object_id($it)] = true;
                continue;
            }

            if ($isAddon) {
                // we'll add addons when we add their parent
                continue;
            }

            // parent
            $out[] = $it;
            $already[spl_object_id($it)] = true;

            foreach (($addonsByGroup[$groupId] ?? []) as $a) {
                $out[] = $a;
                $already[spl_object_id($a)] = true;
            }
        }

        // any leftovers (safety)
        foreach ($items as $it) {
            if (!$it instanceof QuoteItem) continue;
            if (!isset($already[spl_object_id($it)])) {
                $out[] = $it;
            }
        }

        return $out;
    }

    private function getOptionValue(QuoteItem $item, string $code): ?string
    {
        $opt = $item->getOptionByCode($code);
        return $opt ? (string)$opt->getValue() : null;
    }
}