<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Cart;

use Amasty\QuickOrder\Model\Cart\Result as CartResult;
use Amasty\QuickOrder\Model\Cart\ResultFactory as CartResultFactory;
use Amasty\QuickOrder\Model\Item\Pager;
use Amasty\QuickOrder\Model\Item\Validation\Validator;
use Amasty\QuickOrder\Model\Session;
use Amasty\RequestQuote\Model\Cart as QuoteCart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AddProducts implements AddProductsInterface
{
    /**
     * @var CartInterface|Cart|QuoteCart
     */
    private $cart;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Pager
     */
    private $pager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $cartResultFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CartInterface $cart,
        Session $session,
        Validator $validator,
        Pager $pager,
        CartResultFactory $cartResultFactory,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->cart = $cart;
        $this->session = $session;
        $this->validator = $validator;
        $this->pager = $pager;
        $this->messageManager = $messageManager;
        $this->cartResultFactory = $cartResultFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Try adding products from quick order grid into amasty quote cart / magento cart.
     * In error case - return products with errors.
     *
     * @return CartResult
     */
    public function execute(): CartResult
    {
        /** @var CartResult $cartResult */
        $cartResult = $this->cartResultFactory->create();
        $errors = $this->validateCurrentState();

        if (empty($errors)) {
            $items = $this->session->getItems();
            $storeId = $this->storeManager->getStore()->getId();

            foreach ($items as $itemData) {
                try {
                    $product = $this->productRepository->getById((int)$itemData['product_id'], false, $storeId, true);
                    $this->cart->addProduct($product, $itemData);
                    $cartResult->addProduct((int)$itemData['product_id']);
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                    $errors[] = $this->getError($itemData['id'], $message);
                    $this->session->setNotConfigured([
                        $itemData['id'] => $message
                    ]);
                    break;
                }
            }

            if (empty($errors)) {
                $this->cart->save();
                $this->session->clear();

                $count = count($items);
                $this->messageManager->addSuccessMessage(
                    $count == 1
                        ? __('%1 item was added successfully.', $count)
                        : __('%1 items were added successfully.', $count)
                );
            }
        }
        $cartResult->setErrors($errors);

        return $cartResult;
    }

    /**
     * Re-validate current products marked as not configured.
     * Returning array of errors if not configured products confirmed.
     * Stopped at first finding error.
     *
     * @return array
     */
    private function validateCurrentState(): array
    {
        $errors = [];

        $items = $this->session->getItems();

        if ($notConfigured = $this->session->getNotConfigured()) {
            $productIdsForCheck = [];
            foreach ($notConfigured as $itemId => $message) {
                if (!isset($items[$itemId])) {
                    unset($notConfigured[$itemId]);
                    continue;
                }
                $productIdsForCheck[] = $items[$itemId]['product_id'];
            }

            if ($productIdsForCheck) {
                $this->validator->init($productIdsForCheck, 'entity_id');

                foreach ($notConfigured as $itemId => $message) {
                    $result = $this->validator->validate($items[$itemId]);

                    switch ($result->getStatusCode()) {
                        case Validator::ERROR:
                        case Validator::NOT_CONFIGURED:
                            $errors[] = $this->getError($itemId, $result->getMessage());
                            break 2;
                        case Validator::SUCCESS:
                            unset($notConfigured[$itemId]);
                            break;
                    }
                }

                $this->session->setNotConfigured($notConfigured);
            }
        }

        return $errors;
    }

    /**
     * @param int $itemId
     * @param string $message
     * @return array
     */
    private function getError(int $itemId, string $message): array
    {
        return [
            'item_id' => $itemId,
            'page' => $this->getItemPage($itemId),
            'message' => $message
        ];
    }

    /**
     * @param int $itemId
     * @return int
     */
    private function getItemPage(int $itemId): int
    {
        $items = $this->session->getItems();
        $itemPosition = count($items) - array_search($itemId, array_keys($items));

        return $this->pager->getPageByPosition((int)$itemPosition);
    }
}
