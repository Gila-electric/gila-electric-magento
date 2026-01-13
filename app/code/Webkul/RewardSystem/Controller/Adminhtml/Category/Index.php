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

namespace Webkul\RewardSystem\Controller\Adminhtml\Category;

use Webkul\RewardSystem\Controller\Adminhtml\Category as CategoryController;
use Magento\Framework\Controller\ResultFactory;

class Index extends CategoryController
{
    /**
     * Execute method
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_RewardSystem::rewardsystem');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Reward Points on Category'));
        $resultPage->addBreadcrumb(__('Manage Reward Points on Category'), __('Manage Reward Points on Category'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Webkul\RewardSystem\Block\Adminhtml\Category\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                \Webkul\RewardSystem\Block\Adminhtml\Category\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
