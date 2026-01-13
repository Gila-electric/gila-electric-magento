<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class CategoryMode
{
    public const QUICKORDER_MODE = 'quick-order';
    public const SEARCH_ACTION_PAGE = 'catalogsearch_result_index';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Layer
     */
    private $catalogLayer;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request,
        CustomerSession $customerSession,
        Resolver $layerResolver,
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->catalogLayer = $layerResolver->get();
        $this->request = $request;
    }

    public function isAvailable(): bool
    {
        try {
            $result = $this->configProvider->isTableModeEnabled() && $this->configProvider->isGroupEnabledForTableMode(
                (int) $this->customerSession->getCustomerGroupId()
            );

            if ($result) {
                if ($this->isSearchPage()) {
                    $result = $this->configProvider->isTableModeEnabledOnSearch();
                } else {
                    $result = $this->configProvider->isCategoryEnabledForTableMode(
                        (int) $this->catalogLayer->getCurrentCategory()->getId()
                    );
                }
            }
        } catch (LocalizedException $e) {
            $result = false;
        }

        return $result;
    }

    public function getReplacementType(): int
    {
        return $this->configProvider->getReplacementType();
    }

    public function getValue(): array
    {
        return [static::QUICKORDER_MODE => __('Table')];
    }

    public function getAvailableLimit(): array
    {
        $limit = $this->configProvider->getLimitForCategory();

        $limit = array_combine($limit, $limit);

        if ($this->configProvider->isLimitAllowAll()) {
            $limit += ['all' => __('All')];
        }

        return $limit;
    }

    public function getDefaultLimit(): int
    {
        return $this->configProvider->getDefaultLimit();
    }

    private function isSearchPage(): bool
    {
        return $this->request->getFullActionName() === self::SEARCH_ACTION_PAGE;
    }
}
