<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Import;

use Amasty\QuickOrder\Controller\Item\AbstractAction as ItemAction;
use Exception;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Zend\Http\Response;

abstract class AbstractAction extends ItemAction
{
    /**
     * Try import and save products in temp storage.
     * Return array or errors.
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    abstract public function importAction(): array;

    /**
     * @return int
     */
    abstract public function calculateTotalQty(): int;

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
            $result = [
                'errors' => $this->importAction(),
                'total_qty' => $this->calculateTotalQty()
            ];
            if (empty($result['errors'])) {
                $addedItems = $this->getItemManager()->moveTemp();
                $result['items'] = $this->getProductProvider()->getProductsInfo($addedItems);
            }
            return $this->generateResult(Response::STATUS_CODE_200, $result);
        } catch (LocalizedException $e) {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape($e->getMessage())
            ]);
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Something went wrong. Please review the error log.'))
            ]);
        }
    }
}
