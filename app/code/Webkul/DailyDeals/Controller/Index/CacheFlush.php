<?php
/**
 * Webkul_DailyDeals Collection controller.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class CacheFlush extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $dailyDealHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     */
    public function __construct(
        Context $context,
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper
    ) {
        $this->dailyDealHelper = $dailyDealHelper;
        parent::__construct($context);
    }

    /**
     * DailyDeals Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->dailyDealHelper->cacheFlush();
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->dailyDealHelper
                ->jsonEncode(
                    [
                        'success' => 1
                    ]
                ));
        } catch (\Exception $e) {
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->dailyDealHelper
                ->jsonEncode(
                    [
                        'success' => 0,
                        'message' => __('Something went wrong in getting spin wheel.')
                    ]
                ));
        }
    }
}
