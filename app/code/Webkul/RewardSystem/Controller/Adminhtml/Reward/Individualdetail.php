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
use Magento\Backend\App\Action\Context;

class Individualdetail extends RewardController
{

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Customer\Model\CustomerFactory      $customerModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerModel
    ) {
        parent::__construct($context);
        $this->_customerModel = $customerModel;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customer = $this->_customerModel
            ->create()
            ->load($params['customer_id']);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_RewardSystem::rewardsystem');
        $resultPage->getConfig()->getTitle()
            ->prepend(
                __(
                    "%1's Details",
                    $customer->getName()
                )
            );
        $resultPage->addBreadcrumb(
            __("%1's Details", $customer->getName()),
            __("%1's Details", $customer->getName())
        );
        return $resultPage;
    }
}
