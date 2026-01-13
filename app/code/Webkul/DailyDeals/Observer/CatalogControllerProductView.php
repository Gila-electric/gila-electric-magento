<?php
namespace Webkul\DailyDeals\Observer;

/**
 * Webkul_DailyDeals Product View Observer.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\Event\ObserverInterface;
use Webkul\DailyDeals\Helper\Data as DailyDealsHelperData;

/**
 * Reports Event observer model.
 */
class CatalogControllerProductView implements ObserverInterface
{
     /**
      * @var DailyDealsHelperData
      */
    private $dailyDealsHelperData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param DailyDealsHelperData $dailyDealsHelperData
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        DailyDealsHelperData $dailyDealsHelperData,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dailyDealsHelperData = $dailyDealsHelperData;
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
        
        $product = $observer->getEvent()->getProduct();
        $dealDetail = $this->dailyDealsHelperData->getProductDealDetail($product);
        return $this;
    }
}
