<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item;

use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Model\Item\Validation\Validator;
use Amasty\QuickOrder\Model\Session;
use Magento\Framework\Message\ManagerInterface;

class Manager
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var IdGenerator
     */
    private $idGenerator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Fields which always must be rewrite in update item process.
     *
     * @var array
     */
    private $rewritableFields;

    public function __construct(
        Validator $validator,
        IdGenerator $idGenerator,
        Session $session,
        ConfigProvider $configProvider,
        ManagerInterface $messageManager,
        array $rewritableFields = []
    ) {
        $this->validator = $validator;
        $this->idGenerator = $idGenerator;
        $this->session = $session;
        $this->configProvider = $configProvider;
        $this->messageManager = $messageManager;
        $this->rewritableFields = $rewritableFields;
    }

    /**
     * @param array $itemsData
     * @param bool $skipErrors
     * @return array
     */
    public function addItems(array $itemsData, bool $skipErrors = false): array
    {
        $this->validator->init(array_column($itemsData, 'sku'), 'sku');

        $invalidItems = [];
        $notConfiguredItems = [];
        $validatedData = [];

        foreach ($itemsData as $itemData) {
            $result = $this->validator->validate($itemData);

            if (isset($itemData['id'])) {
                $itemId = $itemData['id'];
            } else {
                $itemId = $this->idGenerator->getUid();
                $itemData['id'] = $itemId;
            }

            switch ($result->getStatusCode()) {
                case Validator::NOT_CONFIGURED:
                    $notConfiguredItems[$itemId] = $result->getMessage();
                // no break , not configured product must added on grid without options
                case Validator::SUCCESS:
                    $itemData['product_id'] = $result->getProductId();
                    $validatedData[$itemId] = $itemData;
                    break;
                case Validator::ERROR:
                    if (!$skipErrors) {
                        $invalidItems[$itemId] = [
                            'row' => $itemData['position'] ?? 0,
                            'sku' => $itemData['sku'],
                            'message' => $result->getMessage()
                        ];
                    }
                    break;
            }
        }
        $this->session->addNotConfigured($notConfiguredItems);
        $this->session->setTempItems($validatedData);

        return $invalidItems;
    }

    /**
     * @param array $itemData
     * @return array
     */
    public function addItem(array $itemData): array
    {
        return $this->addItems([$itemData]);
    }

    /**
     * @param int $itemId
     * @param array $itemData
     * @return array
     */
    public function updateItem(int $itemId, array $newData): array
    {
        $itemData = $this->session->getItem($itemId);
        foreach ($this->rewritableFields as $rewritableField) {
            unset($itemData[$rewritableField]);
        }
        foreach ($newData as $key => $value) {
            $itemData[$key] = $value;
        }

        $result = $this->validator->validate($itemData);
        switch ($result->getStatusCode()) {
            case Validator::NOT_CONFIGURED:
                $this->session->updateNotConfigured($itemId, $result->getMessage());
                break;
            case Validator::SUCCESS:
                $this->session->removeNotConfigured($itemId);
                break;
        }

        return $this->session->setItem($itemId, $itemData);
    }

    /**
     * If future items qty in grid exceed MAX_QTY_TO_ADD , slice excess
     *
     * @return array
     */
    public function moveTemp(): array
    {
        $tempItems = $this->session->getTempItems();

        $exceedQty = count($this->session->getItems()) + count($tempItems)
            - $this->configProvider->getMaxQtyToAdd();

        if ($exceedQty > 0) {
            $tempItems = array_slice($tempItems, 0, -1 * $exceedQty, true);
            $this->session->setTempItems($tempItems);
            $this->messageManager->addNoticeMessage(
                __(
                    'The maximum number of items allowed to add is %1.'
                    . ' The items that exceed the limit will not be added.',
                    $this->configProvider->getMaxQtyToAdd()
                )
            );
        }

        return $this->session->moveTemp();
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public function removeItem(int $itemId): bool
    {
        $this->removeItemData($itemId);
        return true;
    }

    /**
     * @return bool
     */
    public function removeAllItems(): bool
    {
        $this->session->clear();
        return true;
    }

    /**
     * @return bool
     */
    public function getItemsCount(): int
    {
        return count($this->session->getItems());
    }

    public function getAllItems(): array
    {
        return $this->session->getItems();
    }

    /**
     * @param int $itemId
     */
    private function removeItemData(int $itemId)
    {
        $this->session->removeNotConfigured($itemId);
        $this->session->removeItem($itemId);
    }
}
