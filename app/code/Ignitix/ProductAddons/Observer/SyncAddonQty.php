<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class SyncAddonQty implements ObserverInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function execute(Observer $observer): void
    {
        $cart = $observer->getEvent()->getCart();
        if (!$cart) {
            return;
        }

        $quote = $cart->getQuote();
        if (!$quote || !$quote->getId()) {
            return;
        }

        $parentQtyByGroup = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item instanceof QuoteItem) continue;

            $groupId = $this->getOptionValue($item, 'ignitix_addon_group_id');
            if (!$groupId) continue;

            $isAddon = $this->getOptionValue($item, 'ignitix_is_addon') === '1';
            if (!$isAddon) {
                $parentQtyByGroup[$groupId] = (float)$item->getQty();
            }
        }

        $changed = false;

        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item instanceof QuoteItem) continue;

            $groupId = $this->getOptionValue($item, 'ignitix_addon_group_id');
            if (!$groupId) continue;

            $isAddon = $this->getOptionValue($item, 'ignitix_is_addon') === '1';
            if (!$isAddon) continue;

            $parentQty = $parentQtyByGroup[$groupId] ?? null;
            if ($parentQty === null) continue;

            if ((float)$item->getQty() !== (float)$parentQty) {
                $item->setQty($parentQty);
                $changed = true;
            }
        }

        if ($changed) {
            $quote->collectTotals();
            $this->cartRepository->save($quote);
        }
    }

    private function getOptionValue(QuoteItem $item, string $code): ?string
    {
        $opt = $item->getOptionByCode($code);
        return $opt ? (string)$opt->getValue() : null;
    }
}