<?php
namespace Ignitix\Hreflang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Hreflang extends Template
{
    protected $storeManager;
    protected $urlBuilder;
    protected $registry;
    protected $request;
    protected $localeResolver;
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Registry $registry,
        HttpRequest $request,
        ResolverInterface $localeResolver,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager   = $storeManager;
        $this->urlBuilder     = $urlBuilder;
        $this->registry       = $registry;
        $this->request        = $request;
        $this->localeResolver = $localeResolver;
        $this->scopeConfig    = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Build alternate links for all active stores and ALWAYS append x-default.
     * - x-default = same href as en-us if present
     * - otherwise = first generated href
     */
    public function getAlternateLinks(): array
    {
        $alternates = [];
        $enHref     = null;

        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $locale = (string) $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            if ($locale === '') {
                continue;
            }

            // Current page URL, but for this store view (HTTPS, pretty URL)
            $href = $this->urlBuilder->getUrl(
                '*/*/*',
                [
                    '_current'     => true,
                    '_use_rewrite' => true,
                    '_scope'       => $store->getId(),
                    '_secure'      => true
                ]
            );

            $hreflang = str_replace('_', '-', strtolower($locale)); // en_US -> en-us

            // remember en-us to mirror for x-default
            if ($enHref === null && $hreflang === 'en-us') {
                $enHref = $href;
            }

            $alternates[] = ['hreflang' => $hreflang, 'href' => $href];
        }

        // Decide x-default and append it
        $xDefaultHref = $enHref ?: ($alternates[0]['href'] ?? null);
        if ($xDefaultHref) {
            $alternates[] = ['hreflang' => 'x-default', 'href' => $xDefaultHref];
        }

        return $alternates;
    }
}