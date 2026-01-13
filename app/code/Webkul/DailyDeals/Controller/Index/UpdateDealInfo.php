<?php
namespace Webkul\DailyDeals\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\DailyDeals\Helper\Data as DailyDealsHelperData;

class UpdateDealInfo extends Action\Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DailyDealsHelperData
     */
    private $dailyDealsHelperData;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param DailyDealsHelperData $dailyDealsHelperData
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        DailyDealsHelperData $dailyDealsHelperData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->dailyDealsHelperData = $dailyDealsHelperData;
        parent::__construct($context);
    }

    /**
     * Update deal detail
     *
     * @return JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        $result = ['status'=> 0];
        if ($data && $data['deal-id']) {
            $product = $this->productRepository->getById($data['deal-id'], true);
            $dealDetail = $this->dailyDealsHelperData->getProductDealDetail($product);
            $result = ['status'=> 1];
        }
        $this->dailyDealsHelperData->cacheFlush();
        return $resultJson->setData($result);
    }
}
