<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Category;

use Amasty\QuickOrder\Model\Category\Item\Manager as ItemManager;
use Magento\Framework\Controller\ResultInterface;
use Zend\Http\Response;
use Zend\Uri\Uri;

class UpdateItem extends AbstractAction
{
    public const ITEM_ID_PARAM = 'item_id';
    public const ITEM_DATA_PARAM = 'item_data';

    protected function action(): ResultInterface
    {
        $itemId = $this->getItemId();
        $itemData = $this->getItemData();
        $uri = $this->getZendUri();
        $uri->setQuery($itemData);
        $itemData = $uri->getQueryAsArray();
        $itemData = $this->convertItemData($itemId, $itemData);
        $this->getItemManager()->update($itemId, $itemData);
        return $this->generateResult(Response::STATUS_CODE_200, []);
    }

    private function getItemId(): int
    {
        return (int) $this->getRequest()->getParam(static::ITEM_ID_PARAM, 0);
    }

    private function getItemData(): string
    {
        return (string) $this->getRequest()->getParam(static::ITEM_DATA_PARAM, []);
    }

    protected function getItemManager(): ItemManager
    {
        return $this->getData('itemManager');
    }

    protected function getZendUri(): Uri
    {
        return $this->getData('zendUri');
    }

    protected function convertItemData(int $itemId, array $itemData): array
    {
        if (isset($itemData['bundle_option'])) {
            $options = [];
            foreach ($itemData['bundle_option'] as $key => $item) {
                if (isset($item[$itemId])) {
                    $options[$key] = $item[$itemId];
                }
            }
            $itemData['bundle_option'] = $options;
        }

        $itemData['checked'] = $itemData['checked'] ?? 0;

        return $itemData;
    }
}
