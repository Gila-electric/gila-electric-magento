<?php
/**
 * Webkul_DailyDeals Product Product Attribute Adminhtml Block.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Block\Adminhtml;

class ProductSetAttribute extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get Deals Date Time
     *
     * @return void
     */
    public function getDealsDateTime()
    {
        $product = $this->coreRegistry->registry('product');
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $dateFrom = $product->getDealFromDate();
        $dateTo = $product->getDealToDate();
        $dealStatus = $product->getDealStatus();
        $proType = $this->getRequest()->getParam('type');
        $proType = $proType ? $proType : $product->getTypeId();
        $dailyDealValue =  [
                'deal_from_date'=> $dealStatus && $dateFrom ? $this->_localeDate->date($dateFrom)
                ->format('m/d/Y H:i:s') :'',
                'deal_to_date'=> $dealStatus && $dateTo ? $this->_localeDate->date($dateTo)
                ->format('m/d/Y H:i:s'):'',
                'date_format' => $dateFormat,
                'module_enable' => true,
                'product_type' => $proType
            ];
        return $dailyDealValue;
    }
}
