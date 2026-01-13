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

namespace Webkul\RewardSystem\Controller\Adminhtml\Product;

use Webkul\RewardSystem\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ProductController
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
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Reward Points on Product'));
        $resultPage->addBreadcrumb(__('Manage Reward Points on Product'), __('Manage Reward Points on Product'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Webkul\RewardSystem\Block\Adminhtml\Product\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(
                \Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
