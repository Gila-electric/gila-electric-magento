<?php
namespace Accept\Payments\Controller\Methods;

/**
 * Valu Payment Method
 */
use Accept\Payments\Helper\Accepting;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
class ValuAcceptMethod extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $statusFactory;
    protected $order;
    protected $statusResourceFactory;
    protected $resource;
    protected $ValuAccept;
    protected $api_id;
    protected $api_has_iframe;
    protected $api_has_items;
    protected $api_has_delivery;
    protected $api_handles_shipping_fees;
    protected $response;
    protected $checkoutSession;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $order,
        \Accept\Payments\Model\ValuAccept $ValuAccept,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    )
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $context->getResultFactory();
        $this->order = $order;
        $this->ValuAccept = $ValuAccept;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;

        // Gateway unique options
        $this->api_id = "VALUACCEPT";
        $this->api_has_iframe   = true;
        $this->api_has_items    = true;
        $this->api_has_delivery = false;
        $this->api_handles_shipping_fees = true;
    }
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
	}
    public function execute()
    {
        $this->response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $this->response->setHttpResponseCode(200);
        $order_id = $this->checkoutSession->getLastOrderId();

        try{
            $order = $this->order->load($order_id);
            if($order){

//                $iframe_id = $this->valu->getConfigData('iframe_id');

                $categoryIds = [];
                foreach ($this->order->getAllItems() as $item) {
                    $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $categoryIds = array_merge($categoryIds ,$product->getCategoryIds());
                }
//                For Apple offer
                $iframe_id = in_array(901, $categoryIds) ? "353449" : $this->ValuAccept->getConfigData('iframe_id');
                $config = [
                    "api_key" => $this->ValuAccept->getConfigData('api_key'),
                    "integration_id" => in_array(901, $categoryIds) ? "245749" : $this->ValuAccept->getConfigData('integration_id'),
                    "has_iframe" => $this->api_has_iframe,
                    "has_items" => $this->api_has_items,
                    "handles_shipping" => $this->api_handles_shipping_fees,
                    "has_delivery" => $this->api_has_delivery,
                ];

                // Start a helper instance
                $helper = new Accepting($order, $config);

            }else {
                throw new \Exception("<p><b>Fatal Error:</b> Order with the id of ($order_id) was not found!</p>");
            }

            if (!$helper->valid_currency($this->api_id)) {
                throw new \Exception($helper->get_error_response("Store currency is not supported by this payment method.", "DEFAULT"));
            }

            if (!$helper->get_token()) {
                throw new \Exception($helper->get_error_response("Can't obtain auth token.", "DEFAULT"));
            }

            $registered_order = $helper->register_order();

            if (!$registered_order->id) {
                throw new \Exception($helper->get_error_response("Can't register order.", "DEFAULT"));
            }

            $payment_key = $helper->request_payment_key($registered_order->id);

            if (!$payment_key) {
                throw new \Exception($helper->get_error_response("Can't obtain payment key.", "DEFAULT"));
            }

            $iframe_url = "https://accept.paymobsolutions.com/api/acceptance/iframes/$iframe_id?payment_token=$payment_key";

            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
                  ->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
                  ->addStatusHistoryComment( __("Order Created: Awaiting payment") )
                  ->save();

            $this->response->setData([
                'success' => true,
                'iframe_url' => $iframe_url
            ]);

        } catch (\Exception $e) {
            $this->response->setData([
                'success' => false,
                'detail' => $e->getMessage(),
            ]);
        }

        return $this->response;
    }
    protected function addNewOrderStateAndStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => 'incomplete_transaction',
            'label' => 'Incomplete Transaction',
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState('incomplete_transaction', true, true);
    }
}
