<?php
namespace Accept\Payments\Controller\Methods;

/**
 * Wallet Payment Method
 */
use Accept\Payments\Helper\Accepting;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
class WalletMethod extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $statusFactory;
    protected $order;
    protected $statusResourceFactory;
    protected $resource;
    protected $wallet;
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
        \Accept\Payments\Model\Wallet $wallet,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    )
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $context->getResultFactory();
        $this->order = $order;
        $this->wallet = $wallet;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;

        // Gateway unique options
        $this->api_id = "WALLET";
        $this->api_has_iframe   = false;
        $this->api_has_items    = false;
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
        $phone_number = $_GET['walletPhone'];

        try{
            $order = $this->order->load($order_id);
            if($order){
                // config to be sent to the Accepting helper.
                $config = [
                    "api_key" => $this->wallet->getConfigData('api_key'),
                    "integration_id" => $this->wallet->getConfigData('integration_id'),
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

            if (!$phone_number || empty($phone_number) || !is_numeric($phone_number) || strlen($phone_number) < 10) {
                throw new \Exception($helper->get_error_response("Invalid phone number.", "PHONE"));
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

            $wallet_url = $helper->request_wallet_url($phone_number);
            
            if (!$wallet_url) {
                throw new \Exception($helper->get_error_response("Can't obtain wallet payment url.", "DEFAULT"));
            }

            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
                  ->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)->save();
            $order->addStatusHistoryComment( __("Order Created: Awaiting payment") )->save();

            $this->response->setData([
                'success' => true,
                'wallet_url' => $wallet_url
            ]);

        } catch (\Exception $e) {
            if (isset($order)){
                if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
                    $order->registerCancellation($e->getMessage())->save();
                }
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
                $quote = $quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null)->save();
            }
            $this->response->setData([
                'success' => false,
                'message' => $e->getMessage(),
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
