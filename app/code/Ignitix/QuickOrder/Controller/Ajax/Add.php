<?php
namespace Ignitix\QuickOrder\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;

class Add extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $cart;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $itemsJson = $this->getRequest()->getParam('items');
        $items = json_decode($itemsJson, true);
        $errors = [];

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->get($item['product']);
                if ($product->getTypeId() !== 'simple') {
                    $errors[] = $item['product'] . ' is not a simple product.';
                    continue;
                }

                $params = [
                    'product' => $product->getId(),
                    'qty' => $item['qty']
                ];

                $this->cart->addProduct($product, $params);
            } catch (\Exception $e) {
                $errors[] = $item['product'] . ' error: ' . $e->getMessage();
            }
        }

        $this->cart->save();

        return $result->setData([
            'success' => empty($errors),
            'message' => implode(', ', $errors)
        ]);
    }
}
