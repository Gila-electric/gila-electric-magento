<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item;

use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Model\Session;

class Pager
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $sessionManager;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $totalPages;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var array|null
     */
    private $allItems;

    public function __construct(
        ConfigProvider $configProvider,
        Session $sessionManager
    ) {
        $this->configProvider = $configProvider;
        $this->sessionManager = $sessionManager;
        $this->init();
    }

    private function init()
    {
        $this->pageSize = $this->configProvider->getPageSize();
    }

    /**
     * @param int $page
     * @return array
     */
    public function getItems(int $page): array
    {
        if (!isset($this->items[$page])) {
            if ($page > $this->getLastPage()) {
                $page = $this->getLastPage();
            } elseif ($page < 1) {
                $page = 1;
            }
            $offset = ($page - 1) * $this->pageSize;

            $this->items[$page] = array_slice($this->getAllItems(), $offset, $this->pageSize);
        }

        return $this->items[$page];
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        if ($this->totalPages === null) {
            $this->totalPages = $this->calculatePage(count($this->getAllItems()));
        }

        return $this->totalPages;
    }

    /**
     * @param int $position
     * @return int
     */
    public function getPageByPosition(int $position)
    {
        return $this->calculatePage($position);
    }

    /**
     * @param int $position
     * @return int
     */
    private function calculatePage(int $position)
    {
        if ($position <= 0) {
            $page = 1;
        } else {
            $page = (int) ceil($position / $this->pageSize);
        }

        return $page;
    }

    /**
     * @return array
     */
    public function getAllItems(): array
    {
        if ($this->allItems === null) {
            $this->allItems = $this->sessionManager->getItems();
        }

        return $this->allItems;
    }
}
