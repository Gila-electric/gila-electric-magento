<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Move;

use Amasty\QuickOrder\Controller\Item\AbstractAction as ItemAction;
use Amasty\QuickOrder\Model\Cart\AddProductsPool;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Zend\Http\Response;

abstract class AbstractAction extends ItemAction
{
    public const ADD_PRODUCTS_MODE = 'add_mode';

    /**
     * @return string
     */
    abstract public function getRedirectAction(): string;

    /**
     * @return ResultInterface
     */
    public function action()
    {
        if (!$this->getRequest()->isPOST()) {
            return $this->generateResult(
                Response::STATUS_CODE_400,
                ['message' => $this->escape(__('Request method type not supported.'))]
            );
        }

        $cartResult = $this->getAddProducts()->get($this->getMode())->execute();

        $result = [
            'errors' => $cartResult->getErrors(),
            'action' => $this->getRedirectAction()
        ];

        if ($cartResult->getCountAddedProducts() && $this->getRedirectAction()) {
            $result['redirect'] = $this->getUrlBuilder()->getUrl($this->getRedirectAction());
        }

        return $this->generateResult(Response::STATUS_CODE_200, $result);
    }

    protected function getMode(): string
    {
        return (string) $this->getRequest()->getParam(
            static::ADD_PRODUCTS_MODE,
            AddProductsPool::FROM_GRID
        );
    }

    /**
     * @return UrlInterface|null
     */
    private function getUrlBuilder()
    {
        return $this->getData('urlBuilder');
    }

    protected function getAddProducts(): ?AddProductsPool
    {
        return $this->getData('addProducts');
    }
}
