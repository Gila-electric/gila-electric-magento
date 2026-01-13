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

namespace Webkul\RewardSystem\Controller\Adminhtml\Reward;

use Webkul\RewardSystem\Controller\Adminhtml\Reward as RewardController;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends RewardController
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_RewardSystem::rewardsystem');
        $resultPage->getConfig()->getTitle()->prepend(__('New Transaction'));
        $resultPage->addBreadcrumb(__('New Transaction'), __('New Transaction'));
        return $resultPage;
    }
}
