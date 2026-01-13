<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Controller\Prize;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Ignitix\BlackFriday25\Helper\Data as Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Preselect extends Action implements HttpGetActionInterface
{
    private JsonFactory $jsonFactory;
    private CheckoutSession $checkout;
    private Helper $helper;
    private ProductRepositoryInterface $products;
    private ImageHelper $image;
    private CartRepositoryInterface $quoteRepository;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CheckoutSession $checkout,
        Helper $helper,
        ProductRepositoryInterface $products,
        ImageHelper $imageHelper,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory     = $jsonFactory;
        $this->checkout        = $checkout;
        $this->helper          = $helper;
        $this->products        = $products;
        $this->image           = $imageHelper;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $res = $this->jsonFactory->create();

        try {
            $quote = $this->checkout->getQuote();
            if (!$quote || !$quote->getId()) {
                throw new LocalizedException(__('No active cart.'));
            }
            if (!$this->helper->isEnabled() || !$this->helper->isInDateRange()) {
                throw new LocalizedException(__('Promotion not applicable.'));
            }

            // Prefer subtotal-based eligibility if helper provides it; fallback to old behavior.
            $eligible = false;
            if (\method_exists($this->helper, 'isPromotionApplicableForQuote')) {
                $eligible = $this->helper->isPromotionApplicableForQuote($quote);
            } else {
                $eligible = $this->helper->isPromotionApplicable((float)$quote->getGrandTotal());
            }
            if (!$eligible) {
                throw new LocalizedException(__('Promotion not applicable.'));
            }

            if ((int)$quote->getData('ignitix_bf25_applied') === 1) {
                throw new LocalizedException(__('Prize already applied.'));
            }

            // Build the CURRENT allowed pool for this quote (handles category-tier & high-tier gates)
            $pool = $this->helper->buildPrizePool($quote);
            if (empty($pool)) {
                throw new LocalizedException(__('No prize is available for the current cart.'));
            }

            $allowedSkus = [];
            foreach ($pool as $row) {
                $sku = isset($row['sku']) ? (string)$row['sku'] : '';
                if ($sku !== '') { $allowedSkus[$sku] = true; }
            }

            $product    = null;
            $pendingSku = (string)$quote->getData('ignitix_bf25_pending_sku');
            $storeId    = (int)$quote->getStoreId();

            // Reuse pending SKU only if still allowed by current pool AND salable (in the same store)
            if ($pendingSku !== '' && isset($allowedSkus[$pendingSku])) {
                try {
                    $candidate = $this->products->get($pendingSku, false, $storeId, true);
                    if ($candidate && $candidate->getId() && $candidate->isSalable()) {
                        $product = $candidate;
                    }
                } catch (\Throwable $e) {
                    // fall through to redraw
                }
            }

            // If no valid pending, draw a new product using helper (ignores OOS + applies weights)
            if ($product === null) {
                $product = $this->helper->drawPrizeProduct($quote);
                if (!$product || !$product->getId()) {
                    throw new LocalizedException(__('No prize could be selected.'));
                }
                // Persist newly selected pending SKU (this will be a salable simple/virtual)
                $quote->setData('ignitix_bf25_pending_sku', (string)$product->getSku());
                $this->quoteRepository->save($quote);
            }

            $img = $this->image->init($product, 'product_small_image')->resize(240, 240)->getUrl();

            return $res->setData([
                'success' => true,
                'product' => [
                    'sku'   => (string)$product->getSku(),
                    'id'    => (int)$product->getId(),
                    'name'  => (string)$product->getName(),
                    'image' => $img,
                ],
            ]);
        } catch (\Throwable $e) {
            return $res->setHttpResponseCode(400)->setData([
                'success' => false,
                'message' => __($e->getMessage()),
            ]);
        }
    }
}