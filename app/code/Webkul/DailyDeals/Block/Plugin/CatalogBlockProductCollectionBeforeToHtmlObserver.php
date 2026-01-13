<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Block\Plugin;

use \Magento\Framework\App\Helper\Context;

class CatalogBlockProductCollectionBeforeToHtmlObserver
{
    /**
     *
     * @var \Webkul\DailyDeal\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Webkul\DailyDeals\Helper\Data $data
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Webkul\DailyDeals\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_helper = $data;
        $this->_reviewFactory = $reviewFactory;
        $this->_request = $request;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $productCollection = $observer->getEvent()->getCollection();
        if (strtolower($this->_request->getFullActionName()) == "dailydeals_index_index") {
            $dealProductIds = $this->_helper->getDealProductIds();
            $productCollection->addAttributeToFilter('entity_id', ['in' => $dealProductIds]);
            $observer->getEvent()->setCollection($productCollection);
        }
        $proceed($observer);
        return $this;
    }
}
