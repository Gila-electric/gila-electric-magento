<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item;

use Magento\Framework\Controller\ResultInterface;
use Zend\Http\Response;

class Remove extends AbstractAction
{
    public const INPUT_NAME = 'item_id';

    /**
     * @return ResultInterface
     */
    protected function action()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Request method type not supported.'))
            ]);
        }

        if ($itemId = $this->getItemId()) {
            return $this->generateResult(Response::STATUS_CODE_200, [
                'result' => $this->getItemManager()->removeItem($itemId)
            ]);
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
        return (int) $this->getRequest()->getParam(static::INPUT_NAME, 0);
    }
}
