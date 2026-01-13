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
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardattributeFactory;

class Edit extends AttributeController
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Webkul\RewardSystem\Model\RewardattributeFactory
     */
    protected $_rewardattributeFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     * @param RewardattributeFactory                     $rewardattributeFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        RewardattributeFactory $rewardattributeFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_rewardattributeFactory = $rewardattributeFactory;
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_RewardSystem::rewardsystem')
            ->addBreadcrumb(__('Reward System Attribute Rule'), __('Reward System Attribute Rule'));
        return $resultPage;
    }
    /**
     * Execute()
     */
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('id');
        $model = $this->_rewardattributeFactory
            ->create();
        if ($id) {
            $model->load($id);
            $flag = 1;
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/index');
            }
        }
        $data = $this->_session
                ->getFormData(true);

        if (isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        $this->_coreRegistry->register('reward_attributerewardData', $model);
        $resultPage = $this->_initAction();
        if ($flag==1 && $id) {
            $resultPage->addBreadcrumb(__('Edit Reward Attribute Rule'), __('Edit Reward Attribute Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Reward Attribute Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Reward Attribute Rule'), __('Add Reward Attribute Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Reward Attribute Rule'));
        }
        return $resultPage;
    }
}
