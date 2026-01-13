<?php
/**
 * Webkul DailyDeals CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigProType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $dailyDealHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configProType;

    /**
     * @var ConfigurableProTypeModel
     */
    protected $_configurableProTypeModel;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $grouped;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param TimezoneInterface $localeDate
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param ConfigProType $configProType
     * @param ScopeConfigInterface $scopeInterface
     * @param ConfigurableProTypeModel $configurableProTypeModel
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ConfigProType $configProType,
        ScopeConfigInterface $scopeInterface,
        ConfigurableProTypeModel $configurableProTypeModel,
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->configProType = $configProType;
        $this->scopeConfig = $scopeInterface;
        $this->_configurableProTypeModel = $configurableProTypeModel;
        $this->grouped = $grouped;
        $this->dailyDealHelper = $dailyDealHelper;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productData = $this->request->getParam('product');
        $modEnable = true;
        if ($modEnable) {
            $token = false;
            $dealToDate = !empty($productData['deal_to_date_tmp'])?$productData['deal_to_date_tmp']:'';
            $dealFromDate = !empty($productData['deal_from_date_tmp'])?$productData['deal_from_date_tmp']:'';
            $config = $this->_configurableProTypeModel->getParentIdsByChild($product->getEntityId());
            if (isset($config[0])) {
                if (!empty($productData['deal_status'])) {
                    $token = true;
                } else {
                    $associatedProducts = $this->configProType->getChildrenIds($config[0]);
                    foreach ($associatedProducts[0] as $assProId) {
                        $associatedProduct = $this->productRepository->getById($assProId);
                        if ($associatedProduct->getDealStatus()) {
                            $dealToDate = $associatedProduct->getDealToDate();
                            $dealFromDate = $associatedProduct->getDealFromDate();
                            $token = true;
                            break;
                        }
                        $this->dailyDealHelper->cleanByTags($assProId);
                    }
                }
                $configproduct = $this->productRepository->getById($config[0]);
                $configproduct->setStoreid(0);
                if ($token) {
                    $configproduct->setDealStatus(1);
                    $configproduct->setDealToDate($dealToDate);
                    $configproduct->setDealFromDate($dealFromDate);
                    $configproduct->setDealValue(1);
                    $configproduct->setDealDiscountType('fixed');
                    $this->productRepository->save($configproduct, true);
                } else {
                    $configproduct->setDealStatus(0);
                    $configproduct->setDealToDate('');
                    $configproduct->setDealFromDate('');
                    $configproduct->setDealValue(0);
                    $configproduct->setDealDiscountType('fixed');
                    $this->productRepository->save($configproduct, true);
                }
            }
            $this->dailyDealHelper->cleanByTags($product->getEntityId());
            
        }
        return $this;
    }
}
