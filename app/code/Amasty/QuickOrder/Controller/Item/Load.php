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

class Load extends AbstractAction
{
    public const PAGE_PARAM = 'p';

    /**
     * @return ResultInterface
     */
    public function action()
    {
        if (!$this->getRequest()->isGet()) {
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Request method type not supported.'))
            ]);
        }

        try {
            return $this->generateResult(
                Response::STATUS_CODE_200,
                $this->getProductProvider()->getProductsInfoByPage(
                    (int) $this->getRequest()->getParam(self::PAGE_PARAM, 1)
                )
            );
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            return $this->generateResult(Response::STATUS_CODE_400, [
                'message' => $this->escape(__('Something is wrong.'))
            ]);
        }
    }
}
