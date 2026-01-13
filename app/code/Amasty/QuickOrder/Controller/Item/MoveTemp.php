<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use Zend\Http\Response;

class MoveTemp extends AbstractAction
{
    /**
     * @return ResultInterface
     */
    public function action()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Request method type not supported.'))
            ]);
        }

        try {
            $itemsData = $this->getItemManager()->moveTemp();
            return $this->generateResult(Response::STATUS_CODE_200, $this->getProductProvider()->getProductsInfo(
                $itemsData
            ));
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Something is wrong.'))
            ]);
        }
    }
}
