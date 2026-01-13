<?php
declare(strict_types=1);

namespace Ignitix\CategorySchema\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Schema extends Template
{
    private Registry $registry;
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private CategoryRepositoryInterface $categoryRepository;
    private ProductCollectionFactory $productCollectionFactory;
    private Visibility $productVisibility;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        Visibility $productVisibility,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
    }

    public function getCurrentCategory(): ?Category
    {
        $cat = $this->registry->registry('current_category');
        return ($cat instanceof Category) ? $cat : null;
    }

    /**
     * BreadcrumbList schema:
     * - Position 1: Store name + base URL (per store view)
     * - Position 2..N: Parent categories then current category
     */
    public function getBreadcrumbSchema(): ?array
    {
        $category = $this->getCurrentCategory();
        if (!$category || !$category->getId()) {
            return null;
        }

        $store = $this->storeManager->getStore();
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

        $pathCategories = $this->getCategoryPathExcludingStoreRoot($category, $storeId);

        $items = [];
        $pos = 1;

        // Home / Store root
        $items[] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => $storeName,
            'item' => $baseUrl,
        ];
        $pos++;

        // Category path
        foreach ($pathCategories as $cat) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => (string)$cat->getName(),
                'item' => (string)$cat->getUrl(),
            ];
            $pos++;
        }

        return [
            '@context' => 'https://schema.org/',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * ItemList schema:
     * - If category has subcategories: list them as CollectionPage
     * - Else: list products in this category as Product
     */
    public function getItemListSchema(): ?array
    {
        $category = $this->getCurrentCategory();
        if (!$category || !$category->getId()) {
            return null;
        }

        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();

        $categoryUrl = (string)$category->getUrl();
        $categoryName = (string)$category->getName();

        $items = [];
        $pos = 1;

        // Subcategories (active only) ordered by "position"
        $children = $category->getChildrenCategories();
        $children->setStoreId($storeId);
        $children->addAttributeToSelect(['name', 'url_key', 'url_path'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSort('position', 'ASC');

        if ((int)$children->getSize() > 0) {
            foreach ($children as $child) {
                /** @var Category $child */
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $pos,
                    'item' => [
                        '@type' => 'CollectionPage',
                        'name' => (string)$child->getName(),
                        'url'  => (string)$child->getUrl(),
                    ],
                ];
                $pos++;
            }
        } else {
            // No subcategories -> list products
            $page = max(1, (int)$this->getRequest()->getParam('p', 1));
            $pageSize = (int)$this->scopeConfig->getValue(
                'catalog/frontend/grid_per_page',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($pageSize <= 0) {
                $pageSize = 12;
            }

            $collection = $this->productCollectionFactory->create();
            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect(['name']);
            $collection->addUrlRewrite();
            $collection->addCategoryFilter($category);
            $collection->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED);
            $collection->addAttributeToFilter('visibility', ['in' => $this->productVisibility->getVisibleInCatalogIds()]);
            $collection->setCurPage($page);
            $collection->setPageSize($pageSize);

            foreach ($collection as $product) {
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
        }

        $count = count($items);
        if ($count === 0) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            '@id' => $categoryUrl . '#itemlist',
            'url' => $categoryUrl,
            'name' => $categoryName,
            'itemListOrder' => 'ItemListOrderAscending',
            'numberOfItems' => $count,
            'itemListElement' => $items,
        ];
    }

    /**
     * Returns category path [top..current], excluding the store root category itself.
     */
    private function getCategoryPathExcludingStoreRoot(Category $current, int $storeId): array
    {
        $store = $this->storeManager->getStore($storeId);
        $storeRootId = (int)$store->getRootCategoryId();

        $path = [];
        $cat = $current;

        // Walk up until store root
        while ($cat && $cat->getId() && (int)$cat->getId() !== $storeRootId) {
            $path[] = $cat;

            $parentId = (int)$cat->getParentId();
            if ($parentId <= 0) {
                break;
            }

            try {
                $cat = $this->categoryRepository->get($parentId, $storeId);
            } catch (\Throwable $e) {
                break;
            }
        }

        return array_reverse($path);
    }
}