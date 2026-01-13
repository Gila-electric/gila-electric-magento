<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Controller\Prize;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\OptionFactory as QuoteItemOptionFactory;
use Ignitix\BlackFriday25\Helper\Data as Helper;

class Apply extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private JsonFactory $jsonFactory;
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $quoteRepository;
    private Helper $helper;
    private ConfigurableType $configurableType;
    private QuoteItemOptionFactory $optionFactory;
    private ProductRepositoryInterface $productRepository;
    private ImageHelper $imageHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        Helper $helper,
        ConfigurableType $configurableType,
        QuoteItemOptionFactory $optionFactory,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory       = $jsonFactory;
        $this->checkoutSession   = $checkoutSession;
        $this->quoteRepository   = $quoteRepository;
        $this->helper            = $helper;
        $this->configurableType  = $configurableType;
        $this->optionFactory     = $optionFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper       = $imageHelper;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            /** @var Quote|null $quote */
            $quote = $this->checkoutSession->getQuote();
            if (!$quote || !$quote->getId()) {
                return $result->setHttpResponseCode(400)->setData([
                    'success' => false,
                    'message' => __('No active cart.')
                ]);
            }

            // Module/date guards
            if (!$this->helper->isEnabled() || !$this->helper->isInDateRange()) {
                return $result->setHttpResponseCode(200)->setData([
                    'success' => false,
                    'message' => __('Promotion not applicable.')
                ]);
            }

            // If already applied, return existing gift for UI
            if ((int)$quote->getData('ignitix_bf25_applied') === 1 || $this->hasGiftItem($quote)) {
                $existing = $this->getExistingGiftProduct($quote);
                return $result->setHttpResponseCode(200)->setData([
                    'success' => true,
                    'alreadyApplied' => true,
                    'product' => $existing ? [
                        'sku'   => (string)$existing->getSku(),
                        'name'  => (string)$existing->getName(),
                        'id'    => (int)$existing->getId(),
                        'type'  => (string)$existing->getTypeId(),
                        'image' => $this->buildSmallImageUrl($existing)
                    ] : null
                ]);
            }

            // Eligibility guard (prefer subtotal-excl-gift when available)
            $eligible = \method_exists($this->helper, 'isPromotionApplicableForQuote')
                ? $this->helper->isPromotionApplicableForQuote($quote)
                : $this->helper->isPromotionApplicable((float)$quote->getGrandTotal());
            if (!$eligible) {
                return $result->setHttpResponseCode(200)->setData([
                    'success' => false,
                    'message' => __('Promotion not applicable.')
                ]);
            }

            // --- Build allowed pool (current cart context) ---
            $pool = $this->helper->buildPrizePool($quote);
            $allowedSkus = [];
            foreach ($pool as $row) {
                $sku = isset($row['sku']) ? (string)$row['sku'] : '';
                if ($sku !== '') { $allowedSkus[$sku] = true; }
            }

            // --- Choose product: pending SKU -> posted SKU -> draw ---
            $requestedSku = (string)($this->getRequest()->getParam('sku') ?? '');
            $pendingSku   = (string)$quote->getData('ignitix_bf25_pending_sku');
            $selectedSku  = $pendingSku !== '' ? $pendingSku : $requestedSku;

            $product = null;
            $storeId = (int)$quote->getStoreId();

            if ($selectedSku !== '' && isset($allowedSkus[$selectedSku])) {
                try {
                    $candidate = $this->productRepository->get($selectedSku, false, $storeId, true);
                    if ($candidate && $candidate->getId()) {
                        if ($candidate->getTypeId() === 'configurable') {
                            // Resolve to first salable child
                            $children = $candidate->getTypeInstance()->getUsedProducts($candidate);
                            foreach ($children as $child) {
                                if ($child->isSalable()) {
                                    $product = $this->productRepository->get($child->getSku(), false, $storeId, true);
                                    break;
                                }
                            }
                        } elseif (
                            ($candidate->getTypeId() === 'simple' || $candidate->getTypeId() === 'virtual')
                            && $candidate->isSalable()
                        ) {
                            $product = $candidate;
                        }
                    }
                } catch (\Throwable $e) {
                    $product = null;
                }
            }

            // Fallback: draw (helper enforces pool/gates and salability)
            if (!$product || !$product->getId()) {
                $product = $this->helper->drawPrizeProduct($quote);
            }
            if (!$product || !$product->getId()) {
                throw new LocalizedException(__('No prize could be selected.'));
            }

            // Race guard (re-check just before add)
            if ($this->hasGiftItem($quote)) {
                $existing = $this->getExistingGiftProduct($quote);
                return $result->setHttpResponseCode(200)->setData([
                    'success' => true,
                    'alreadyApplied' => true,
                    'product' => $existing ? [
                        'sku'   => (string)$existing->getSku(),
                        'name'  => (string)$existing->getName(),
                        'id'    => (int)$existing->getId(),
                        'type'  => (string)$existing->getTypeId(),
                        'image' => $this->buildSmallImageUrl($existing)
                    ] : null
                ]);
            }

            // Add one unit as a free gift
            $request   = new DataObject(['qty' => 1, 'product' => (int)$product->getId()]);
            $quoteItem = $quote->addProduct($product, $request);
            if (is_string($quoteItem)) {
                throw new LocalizedException(__($quoteItem));
            }

            // If this simple is a child of a configurable, attach expected options
            $parentIds = $this->configurableType->getParentIdsByChild((int)$product->getId());
            if (!empty($parentIds)) {
                $parentId = (int)$parentIds[0];

                $optCfg = $this->optionFactory->create();
                $optCfg->setProductId($parentId)
                    ->setCode('super_product_config')
                    ->setValue(json_encode([
                        'product_type' => 'configurable',
                        'product_id'   => $parentId
                    ]));
                $quoteItem->addOption($optCfg);

                $optSimple = $this->optionFactory->create();
                $optSimple->setProductId((int)$product->getId())
                    ->setCode('simple_product')
                    ->setValue((string)$product->getId());
                $quoteItem->addOption($optSimple);
            }

            // Mark as gift (option + data flag; data flag persists via db_schema column)
            $optGift = $this->optionFactory->create();
            $optGift->setProductId((int)$product->getId())
                ->setCode('ignitix_bf25_prize')
                ->setValue('1');
            $quoteItem->addOption($optGift);

            // IMPORTANT: set SuperMode BEFORE price overrides
            $quoteItem->getProduct()->setIsSuperMode(true);

            // Force free price + flags
            $quoteItem->setQty(1);
            $quoteItem->setCustomPrice(0.0);
            $quoteItem->setOriginalCustomPrice(0.0);
            $quoteItem->setPrice(0.0);
            $quoteItem->setBasePrice(0.0);
            $quoteItem->setRowTotal(0.0);
            $quoteItem->setBaseRowTotal(0.0);
            $quoteItem->setNoDiscount(true);
            $quoteItem->setIsPriceChanged(true);
            $quoteItem->setData('ignitix_bf25_gift', 1);

            // Mark + snapshot for later change detection
            $quote->setData('ignitix_bf25_applied', 1);
            $quote->setData('ignitix_bf25_prize_sku', (string)$product->getSku());
            $quote->setData('ignitix_bf25_items_hash', $this->helper->getItemsHash($quote));
            $quote->setData('ignitix_bf25_pending_sku', null); // Clear pending once applied

            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            // Small image for UI
            $img = $this->buildSmallImageUrl($product);

            return $result->setHttpResponseCode(200)->setData([
                'success' => true,
                'product' => [
                    'sku'   => (string)$product->getSku(),
                    'name'  => (string)$product->getName(),
                    'id'    => (int)$product->getId(),
                    'type'  => (string)$product->getTypeId(),
                    'image' => $img
                ]
            ]);
        } catch (\Throwable $e) {
            return $result->setHttpResponseCode(500)->setData([
                'success' => false,
                'message' => __('Could not add prize: %1', $e->getMessage())
            ]);
        }
    }

    /** Duplicate guard: detect existing gift by flag/option or zero-priced prize SKU */
    private function hasGiftItem(Quote $quote): bool
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) { continue; }

            if ((int)$item->getData('ignitix_bf25_gift') === 1) {
                return true;
            }
            if ($item->getOptionByCode('ignitix_bf25_prize') || $item->getOptionByCode('bf25_prize')) {
                return true;
            }
            if ((float)$item->getPrice() == 0.0 && (float)$item->getRowTotal() == 0.0) {
                return true;
            }
        }
        return false;
    }

    /** If a gift is already on the quote, return that product (for image/name). */
    private function getExistingGiftProduct(Quote $quote)
    {
        $storeId = (int)$quote->getStoreId();

        // 1) Try explicit prize SKU stored on quote
        $sku = (string)$quote->getData('ignitix_bf25_prize_sku');
        if ($sku !== '') {
            try { return $this->productRepository->get($sku, false, $storeId, true); } catch (\Throwable $e) {}
        }

        // 2) Fallback: scan items
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) { continue; }
            if ((int)$item->getData('ignitix_bf25_gift') === 1
                || $item->getOptionByCode('ignitix_bf25_prize')
                || $item->getOptionByCode('bf25_prize')
                || ((float)$item->getPrice() == 0.0 && (float)$item->getRowTotal() == 0.0)
            ) {
                try { return $this->productRepository->get((string)$item->getSku(), false, $storeId, true); } catch (\Throwable $e) {}
            }
        }
        return null;
    }

    private function buildSmallImageUrl($product): string
    {
        return $this->imageHelper
            ->init($product, 'product_small_image')
            ->resize(240, 240)
            ->getUrl();
    }

    // CSRF: allow AJAX posts (form_key handled client-side)
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException { return null; }
    public function validateForCsrf(RequestInterface $request): ?bool { return true; }
}