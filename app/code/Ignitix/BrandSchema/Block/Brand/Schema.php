<?php
declare(strict_types=1);

namespace Ignitix\BrandSchema\Block\Brand;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Schema extends Template
{
    private const BRAND_PATH_SEGMENT = 'brand';

    private Registry $registry;
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private LayerResolver $layerResolver;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LayerResolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry     = $registry;
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->layerResolver = $layerResolver;
    }

    public function isBrandPage(): bool
    {
        $url = $this->getCurrentUrlNoQuery();
        $path = (string)parse_url($url, PHP_URL_PATH);

        // Matches: /brand , /brand/xxx , /en/brand/xxx , /ar/brand/xxx
        return (bool)preg_match('#(^|/)' . preg_quote(self::BRAND_PATH_SEGMENT, '#') . '(/|$)#', $path);
    }

    public function getBreadcrumbSchema(): ?array
    {
        if (!$this->isBrandPage()) {
            return null;
        }

        $store   = $this->storeManager->getStore();
        $storeId = (int)$store->getId();

        $storeName = (string)$this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($storeName === '') {
            $storeName = (string)$store->getName();
        }

        $baseUrl = (string)$store->getBaseUrl();
        $brandListUrl = rtrim($baseUrl, '/') . '/' . self::BRAND_PATH_SEGMENT;

        $brandName = $this->getBrandDisplayName();
        $brandUrl  = $this->getCurrentUrlNoQuery();

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $storeName,
                'item' => $baseUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => (string)__('Brands'),
                'item' => $brandListUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $brandName,
                'item' => $brandUrl,
            ],
        ];

        return [
            '@context' => 'https://schema.org/',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function getItemListSchema(): ?array
    {
        if (!$this->isBrandPage()) {
            return null;
        }

        $brandName = $this->getBrandDisplayName();
        $brandUrl  = $this->getCurrentUrlNoQuery();

        // Best-effort: use catalog layer collection (Codazon brand pages usually build a product list via layer)
        try {
            $layer = $this->layerResolver->get();
            $collection = $layer->getProductCollection();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$collection) {
            return null;
        }

        // Ensure loaded
        try {
            $collection->load();
        } catch (\Throwable $e) {
            return null;
        }

        $items = [];
        $pos = 1;
		$maxItems = 10;

        foreach ($collection as $product) {
			if ($pos > $maxItems) {
				break;
			}
			
			$items[] = [
				'@type' => 'ListItem',
				'position' => $pos,
				'item' => [
					'@type' => 'Product',
					'name' => (string)$product->getName(),
					'url'  => (string)$product->getProductUrl(),
				],
			];
			$pos++;
        }

        $count = count($items);
        if ($count === 0) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            '@id' => $brandUrl . '#itemlist',
            'url' => $brandUrl,
            'name' => (string)__('%1 Products', $brandName),
            'itemListOrder' => 'ItemListOrderAscending',
            'numberOfItems' => $count,
            'itemListElement' => $items,
        ];
    }

    private function getBrandDisplayName(): string
    {
        // 1) Try Codazon-like registry values (if present)
        foreach (['current_brand', 'brand', 'current_brand_data'] as $key) {
            $val = $this->registry->registry($key);
            if (is_object($val)) {
                foreach (['getName', 'getTitle', 'getLabel'] as $m) {
                    if (method_exists($val, $m)) {
                        $name = (string)$val->{$m}();
                        if (trim($name) !== '') {
                            return trim($name);
                        }
                    }
                }
            } elseif (is_string($val) && trim($val) !== '') {
                return trim($val);
            }
        }

        // 2) Use page title (usually localized per store view)
        $title = (string)$this->pageConfig->getTitle()->get();
        $title = trim($title);
        if ($title !== '') {
            return $title;
        }

        // 3) Fallback: derive from URL key
        $url = $this->getCurrentUrlNoQuery();
        $path = (string)parse_url($url, PHP_URL_PATH);
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $last = end($parts);
        if (is_string($last) && $last !== '') {
            $last = str_replace('-', ' ', $last);
            return trim($last);
        }

        return 'Brand';
    }

    private function getCurrentUrlNoQuery(): string
    {
        $url = (string)$this->_urlBuilder->getCurrentUrl();
        $url = preg_replace('#\?.*$#', '', $url) ?? $url;
        $url = preg_replace('#\#.*$#', '', $url) ?? $url;

        // Remove trailing slash (except if it's just ".../ar/" or ".../en/")
        if (strlen($url) > 1) {
            $url = rtrim($url, '/');
        }
        return $url;
    }
}