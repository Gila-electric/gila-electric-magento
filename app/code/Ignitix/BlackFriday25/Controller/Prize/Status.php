<?php
declare(strict_types=1);

namespace Ignitix\BlackFriday25\Controller\Prize;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Ignitix\BlackFriday25\Helper\Data as Helper;

class Status extends Action implements HttpGetActionInterface, CsrfAwareActionInterface
{
    private JsonFactory $jsonFactory;
    private CheckoutSession $checkoutSession;
    private Helper $helper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CheckoutSession $checkoutSession,
        Helper $helper
    ) {
        parent::__construct($context);
        $this->jsonFactory     = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->helper          = $helper;
    }

    public function execute()
    {
        $res   = $this->jsonFactory->create();
        $quote = $this->checkoutSession->getQuote();

        $gt = ($quote && $quote->getId()) ? (float)$quote->getGrandTotal() : 0.0;

        // Find an already-added gift item (more robust than relying only on quote data flag)
        $giftItem = null;
        if ($quote && $quote->getId()) {
            foreach ($quote->getAllVisibleItems() as $it) {
                if ((int)$it->getData('ignitix_bf25_gift') === 1) {
                    $giftItem = $it;
                    break;
                }
            }
        }

        $applied   = $giftItem !== null || (($quote && (int)$quote->getData('ignitix_bf25_applied') === 1));
        $threshold = (float)$this->helper->getThreshold();
        $inDate    = $this->helper->isEnabled() && $this->helper->isInDateRange();

        // Use subtotal EXCLUDING the gift for eligibility/remaining
        $subtotal = 0.0;
        if ($quote && $quote->getId()) {
            if (\method_exists($this->helper, 'getCartSubtotalExclGift')) {
                $subtotal = (float)$this->helper->getCartSubtotalExclGift($quote);
            } else {
                // Safe fallback if helper wasn’t updated yet
                $subtotal = (float)$quote->getSubtotal();
            }
        }

        // Prefer the helper’s quote-aware eligibility; fallback to subtotal check
        if (\method_exists($this->helper, 'isPromotionApplicableForQuote')) {
            $eligible = $this->helper->isPromotionApplicableForQuote($quote);
        } else {
            $eligible = $inDate && ($subtotal >= $threshold);
        }

        $remaining = max(0.0, $threshold - $subtotal);

        $prize = null;
        if ($giftItem) {
            $prize = [
                'sku'  => (string)($giftItem->getSku() ?: (string)$quote->getData('ignitix_bf25_prize_sku')),
                'name' => (string)$giftItem->getName(),
                'qty'  => (float)$giftItem->getQty()
            ];
        }

        return $res->setData([
            'success'     => true,
            'applied'     => $applied,         // kept for backward compatibility
            'grand_total' => $gt,              // kept for backward compatibility
            'in_date'     => $inDate,
            'threshold'   => $threshold,
            'eligible'    => $eligible,
            'remaining'   => $remaining,
            'prize'       => $prize
        ]);
    }

    // CSRF not enforced for GET; keep no-op hooks
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException { return null; }
    public function validateForCsrf(RequestInterface $request): ?bool { return true; }
}