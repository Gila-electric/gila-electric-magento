<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\QuickOrder\Model\Config\Utils;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;

class ConfigProvider extends ConfigProviderAbstract
{
    public const PATH_PREFIX = 'amasty_quickorder/';

    public const ENABLED_PATH = 'general/enabled';
    public const DISABLED_CUSTOMER_GROUPS_PATH = 'general/disabled_customer_groups';
    public const LABEL_PATH = 'general/label';
    public const URL_KEY_PATH = 'general/url_key';
    public const DISPLAY_LINK_PATH = 'general/display_link_in';
    public const SEARCH_MAX_RESULTS = 'general/search_max_results';
    public const PAGE_SIZE = 'general/page_size';
    public const MAX_QTY_TO_ADD = 'general/max_qty_add';
    public const REQUEST_QUOTE_BUTTON = 'general/request_quote_button';
    public const DOWNLOAD_LIST_PATH = 'general/download_list';
    public const CUSTOMER_GROUPS_DISABLED_FILE_PATH = 'general/customer_groups_disabled_file';
    public const CATEGORY_MODE_ENABLED = 'category_mode/enabled';
    public const CATEGORY_MODE_REPLACE = 'category_mode/replace';
    public const CATEGORY_MODE_LIMIT = 'category_mode/grid_per_page_values';
    public const CATEGORY_MODE_DEFAULT_LIMIT = 'category_mode/grid_per_page';
    public const CATEGORY_MODE_CUSTOMER_GROUP_DISABLED = 'category_mode/disabled_customer_groups';
    public const CATEGORY_MODE_CATEGORIES_DISABLED = 'category_mode/disabled_categories';
    public const CATEGORY_MODE_ON_SEARCH = 'category_mode/enabled_on_search';

    public const REQUEST_QUOTE_IS_ACTIVE_PATH = 'amasty_request_quote/general/is_active';
    public const REQUEST_QUOTE_DISPLAY_FOR_GROUP_PATH = 'amasty_request_quote/general/visible_for_groups';

    public const CATALOG_ALLOW_ALL_PRODUCTS_PAGE = 'catalog/frontend/list_allow_all';

    /**
     * @var string
     */
    protected $pathPrefix = self::PATH_PREFIX;

    /**
     * @var Utils
     */
    private $utils;

    public function __construct(
        Utils $utils,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($scopeConfig);
        $this->utils = $utils;
    }

    /**
     * @return bool
     */
    public function isQuickOrderEnabled(): bool
    {
        return $this->isSetFlag(static::ENABLED_PATH);
    }

    /**
     * @return array
     */
    public function getDisabledCustomerGroups(): array
    {
        return $this->utils->parseMultiselect((string) $this->getValue(static::DISABLED_CUSTOMER_GROUPS_PATH));
    }

    /**
     * @param int $customerGroupId
     * @return bool
     */
    public function isCustomerGroupEnabled(int $customerGroupId): bool
    {
        return !in_array($customerGroupId, $this->getDisabledCustomerGroups());
    }

    /**
     * @return Phrase|string
     */
    public function getLabel()
    {
        $label = (string) $this->getValue(static::LABEL_PATH);
        if (!$label) {
            $label = __('Quick Order');
        }

        return $label;
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        $urlKey = (string) $this->getValue(static::URL_KEY_PATH);
        if (!$urlKey) {
            $urlKey = 'quick-order';
        }

        return $urlKey;
    }

    /**
     * @return array
     */
    public function getDisplayPlaces(): array
    {
        return $this->utils->parseMultiselect((string) $this->getValue(static::DISPLAY_LINK_PATH));
    }

    /**
     * @param int $placeId
     * @return bool
     */
    public function isDisplayLInkIn(int $placeId): bool
    {
        return in_array($placeId, $this->getDisplayPlaces());
    }

    /**
     * @return int
     */
    public function getSearchLimitResults(): int
    {
        return (int) $this->getValue(static::SEARCH_MAX_RESULTS);
    }

    /**
     * @return bool
     */
    public function isMysqlEngine(): bool
    {
        return $this->scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH) === 'mysql';
    }

    /**
     * @return bool
     */
    public function isElasticEngine(): bool
    {
        return strpos($this->scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH), 'elast')
            !== false;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return (int) ($this->getValue(static::PAGE_SIZE) ?: 7);
    }

    /**
     * @return int
     */
    public function getMaxQtyToAdd(): int
    {
        return (int) ($this->getValue(static::MAX_QTY_TO_ADD) ?: 100);
    }

    /**
     * @param int $customerGroupId
     * @return bool
     */
    public function isRequestQuoteEnabled(int $customerGroupId): bool
    {
        $allowedGroups = $this->scopeConfig->getValue(static::REQUEST_QUOTE_DISPLAY_FOR_GROUP_PATH);

        return $this->scopeConfig->getValue(static::REQUEST_QUOTE_IS_ACTIVE_PATH)
            && in_array($customerGroupId, explode(',', $allowedGroups));
    }

    /**
     * @return array
     */
    public function getCustomerGroupsDisabledFile(): array
    {
        return array_filter(
            explode(',', (string) $this->getValue(static::CUSTOMER_GROUPS_DISABLED_FILE_PATH)),
            function ($elem) {
                return trim($elem) != '';
            }
        );
    }

    /**
     * @param int $customerGroupId
     * @return bool
     */
    public function isCustomerCanUploadFile(int $customerGroupId): bool
    {
        return !in_array($customerGroupId, $this->getCustomerGroupsDisabledFile());
    }

    public function isTableModeEnabled(): bool
    {
        return $this->isSetFlag(static::CATEGORY_MODE_ENABLED);
    }

    public function getReplacementType(): int
    {
        return (int) $this->getValue(static::CATEGORY_MODE_REPLACE);
    }

    public function getLimitForCategory(): array
    {
        return explode(',', $this->getValue(static::CATEGORY_MODE_LIMIT));
    }

    public function isLimitAllowAll(): bool
    {
        return $this->scopeConfig->isSetFlag(static::CATALOG_ALLOW_ALL_PRODUCTS_PAGE);
    }

    public function getDefaultLimit(): int
    {
        return (int) $this->getValue(static::CATEGORY_MODE_DEFAULT_LIMIT);
    }

    public function getDisabledGroupsForTableMode(): array
    {
        return $this->utils->parseMultiselect(
            (string) $this->getValue(static::CATEGORY_MODE_CUSTOMER_GROUP_DISABLED)
        );
    }

    public function isGroupEnabledForTableMode(int $customerGroupId): bool
    {
        return !in_array($customerGroupId, $this->getDisabledGroupsForTableMode());
    }

    public function getDisabledCategoriesForTableMode(): array
    {
        return $this->utils->parseMultiselect(
            (string) $this->getValue(static::CATEGORY_MODE_CATEGORIES_DISABLED)
        );
    }

    public function isCategoryEnabledForTableMode(int $categoryId): bool
    {
        return !in_array($categoryId, $this->getDisabledCategoriesForTableMode());
    }

    public function isRequestQuoteButtonEnabled(): bool
    {
        return $this->isSetFlag(static::REQUEST_QUOTE_BUTTON);
    }

    public function isTableModeEnabledOnSearch(): bool
    {
        return $this->isSetFlag(static::CATEGORY_MODE_ON_SEARCH);
    }

    public function isDownloadListAllowed(): bool
    {
        return $this->isSetFlag(static::DOWNLOAD_LIST_PATH);
    }
}
