<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManager;

class Session extends SessionManager
{
    public const ITEMS = 'current_items';
    public const PRODUCT_IDS = 'current_product_ids';
    public const LAST_ID = 'last_id';
    public const TEMP_ITEMS = 'temp_items';
    public const NOT_CONFIGURED_ITEMS = 'not_configured_items';

    /**
     * @param array $items
     * @return $this
     */
    public function addItems(array $items): Session
    {
        $this->setProductIds(array_unique(array_merge(
            $this->getProductIds(),
            array_column($items, 'product_id')
        )));

        $this->setItems(($this->getItems() + $items));

        return $this;
    }

    /**
     * @param $items
     * @return $this
     */
    public function setItems(array $items): Session
    {
        $this->setData(self::ITEMS, $items);
        return $this;
    }

    /**
     * @param int $itemId
     * @param array $data
     *
     * @return array
     */
    public function setItem(int $itemId, array $data): array
    {
        $items = $this->getItems();
        $items[$itemId] = $data;
        $this->setItems($items);

        return $data;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->getData(self::ITEMS) ?: [];
    }

    /**
     * @param int $itemId
     * @return Session
     */
    public function removeItem(int $itemId): Session
    {
        $items = $this->getItems();
        unset($items[$itemId]);

        return $this->setItems($items);
    }

    /**
     * @param int $itemId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItem(int $itemId): array
    {
        $items = $this->getItems();
        if (!isset($items[$itemId])) {
            throw new NoSuchEntityException(
                __('Item was not found')
            );
        }

        return $items[$itemId];
    }

    /**
     * @param $productIds
     * @return $this
     */
    public function setProductIds(array $productIds): Session
    {
        $this->setData(self::PRODUCT_IDS, $productIds);
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->getData(self::PRODUCT_IDS) ?: [];
    }

    /**
     * @param array $items
     * @return $this
     */
    public function addNotConfigured(array $items): Session
    {
        $this->setNotConfigured(($this->getNotConfigured() + $items));

        return $this;
    }

    /**
     * @param $items
     * @return $this
     */
    public function setNotConfigured(array $items): Session
    {
        $this->setData(self::NOT_CONFIGURED_ITEMS, $items);
        return $this;
    }

    /**
     * @return array
     */
    public function getNotConfigured(): array
    {
        return $this->getData(self::NOT_CONFIGURED_ITEMS) ?: [];
    }

    /**
     * @param int $itemId
     * @return Session
     */
    public function removeNotConfigured(int $itemId): Session
    {
        $notConfigured = $this->getNotConfigured();
        unset($notConfigured[$itemId]);

        return $this->setNotConfigured($notConfigured);
    }

    /**
     * @param int $itemId
     * @param string $message
     * @return Session
     */
    public function updateNotConfigured(int $itemId, string $message): Session
    {
        $notConfigured = $this->getNotConfigured();
        $notConfigured[$itemId] = $message;

        return $this->setNotConfigured($notConfigured);
    }

    /**
     * @param int $lastId
     * @return $this
     */
    public function setLastId(int $lastId): Session
    {
        $this->setData(self::LAST_ID, $lastId);
        return $this;
    }

    /**
     * @return int
     */
    public function getLastId(): int
    {
        return $this->getData(self::LAST_ID) ?: 0;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setTempItems(array $items)
    {
        $this->setData(self::TEMP_ITEMS, $items);
        return $this;
    }

    /**
     * @return array
     */
    public function getTempItems(): array
    {
        return $this->getData(self::TEMP_ITEMS) ?: [];
    }

    /**
     * @return array
     */
    public function moveTemp(): array
    {
        $tempItems = $this->getTempItems();
        $this->addItems($tempItems);
        $this->setTempItems([]);

        return $tempItems;
    }

    public function clear()
    {
        $this->setItems([]);
        $this->setNotConfigured([]);
        $this->setTempItems([]);
    }
}
