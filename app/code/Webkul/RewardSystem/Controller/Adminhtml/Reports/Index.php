<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Controller\Adminhtml\Reports;

use Webkul\RewardSystem\Controller\Adminhtml\Reports as ReportsController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ReportsController
{
    /**
     * Execute method
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_RewardSystem::report_rewardsystem');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points Reports'));
        $resultPage->addBreadcrumb(__('Reward Points Reports'), __('Reward Points Reports'));
        return $resultPage;
    }
}
