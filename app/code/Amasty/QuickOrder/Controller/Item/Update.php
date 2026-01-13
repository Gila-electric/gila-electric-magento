<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend\Http\Response;

class Update extends AbstractAction
{
    public const ITEM_ID_PARAM = 'item_id';
    public const ITEM_DATA_PARAM = 'item_data';

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function action()
    {
        if ($itemId = $this->getItemId()) {
            $itemData = $this->getItemData();
            $uri = $this->getZendUri();
            $uri->setQuery($itemData);
            $itemData = $uri->getQueryAsArray();
            $itemData = $this->convertItemData($itemData);
            try {
                $item = $this->getItemManager()->updateItem($itemId, $itemData);
                return $this->generateResult(Response::STATUS_CODE_200, [
                    'result' => $this->getProductProvider()->getProductsInfo([$item])
                ]);
            } catch (NoSuchEntityException $exception) {
                return $this->generateResult(Response::STATUS_CODE_200, [
                    'reloadItems' => true
                ]);
            }

        } else {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Item id not provided.'))
            ]);
        }
    }

    /**
     * @return int
     */
    private function getItemId(): int
    {
        return (int) $this->getRequest()->getParam(static::ITEM_ID_PARAM, 0);
    }

    /**
     * @return string
     */
    private function getItemData(): string
    {
        return (string) $this->getRequest()->getParam(static::ITEM_DATA_PARAM, '');
    }

    /**
     * @param array $itemData
     *
     * @return array
     */
    protected function convertItemData(array $itemData)
    {
        $itemId = $this->getItemId();
        if (isset($itemData['bundle_option'])) {
            $options = [];
            foreach ($itemData['bundle_option'] as $key => $item) {
                if (isset($item[$itemId])) {
                    $options[$key] = $item[$itemId];
                }
            }
            $itemData['bundle_option'] = $options;
        }

        return $itemData;
    }
}
