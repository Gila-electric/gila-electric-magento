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

namespace Webkul\RewardSystem\Controller\Adminhtml\Attribute;

use Webkul\RewardSystem\Controller\Adminhtml\Attribute as AttributeController;
use Magento\Framework\Controller\ResultFactory;

class Index extends AttributeController
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
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Reward Points on Attribute'));
        $resultPage->addBreadcrumb(__('Manage Reward Points on Attribute'), __('Manage Reward Points on Attribute'));
        return $resultPage;
    }
}
