<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Ignitix\BlackFriday25\Helper\Data as Helper;

class ResetPrizeOnCartChange implements ObserverInterface
{
    private Helper $helper;
    /** re-entry guard (per-request) */
    private static bool $processing = false;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer): void
    {
        if (self::$processing) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!$quote || !$quote->getId()) {
            return;
        }

        $applied = (int)$quote->getData('ignitix_bf25_applied') === 1;

        if (!$applied) {
            // Clean stale markers only; do NOT mutate items or trigger collects
            if ($quote->getData('ignitix_bf25_prize_sku') || $quote->getData('ignitix_bf25_items_hash')) {
                $quote->setData('ignitix_bf25_prize_sku', null);
                $quote->setData('ignitix_bf25_items_hash', null);
            }
            // Also clear any stale pending lock
            if ($quote->getData('ignitix_bf25_pending_sku')) {
                $quote->setData('ignitix_bf25_pending_sku', null);
            }
            return;
        }

        // If applied but snapshot missing (first run after update), snapshot and bail.
        if (!$quote->getData('ignitix_bf25_items_hash')) {
            $quote->setData('ignitix_bf25_items_hash', $this->helper->getItemsHash($quote));
            return;
        }

        // If the promo is no longer applicable (date/threshold decreased), reset.
        if (!$this->helper->isPromotionApplicableForQuote($quote)) {
            self::$processing = true;
            $this->removePrizeFromQuote($quote);
            $this->clearFlags($quote);
            self::$processing = false;
            return;
        }

        // Detect cart changes via hash (gift-excluded)
        $currentHash = $this->helper->getItemsHash($quote);
        $storedHash  = (string)$quote->getData('ignitix_bf25_items_hash');

        if ($storedHash !== '' && $currentHash !== $storedHash) {
            self::$processing = true; // guard

            $this->removePrizeFromQuote($quote);
            $this->clearFlags($quote);

            self::$processing = false;
        }
    }

    private function removePrizeFromQuote(\Magento\Quote\Model\Quote $quote): void
    {
        $prizeSku = (string)$quote->getData('ignitix_bf25_prize_sku');
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getSku() === $prizeSku || (int)$item->getData('ignitix_bf25_gift') === 1) {
                $quote->removeItem((int)$item->getId()); // mark for removal; no collectTotals here
            }
        }
        // Let Magento recalc totals later in the normal save flow.
        $quote->setTriggerRecollect(true);
    }

    private function clearFlags(\Magento\Quote\Model\Quote $quote): void
    {
        $quote->setData('ignitix_bf25_applied', 0);
        $quote->setData('ignitix_bf25_prize_sku', null);
        $quote->setData('ignitix_bf25_items_hash', null);
        $quote->setData('ignitix_bf25_pending_sku', null);
    }
}