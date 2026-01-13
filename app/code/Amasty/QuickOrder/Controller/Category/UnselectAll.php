<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Category;

use Amasty\QuickOrder\Model\Category\Item\Manager as ItemManager;
use Exception;
use Magento\Framework\Controller\ResultInterface;
use Zend\Http\Response;

class UnselectAll extends AbstractAction
{
    protected function action(): ResultInterface
    {
        if (!$this->getRequest()->isPost()) {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Request method type not supported.'))
            ]);
        }

        try {
            $this->getItemManager()->clear();

            return $this->generateResult(
                Response::STATUS_CODE_200,
                []
            );
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Something is wrong.'))
            ]);
        }
    }

    protected function getItemManager(): ItemManager
    {
        return $this->getData('itemManager');
    }
}
