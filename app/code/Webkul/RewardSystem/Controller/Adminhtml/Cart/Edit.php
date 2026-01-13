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

namespace Webkul\RewardSystem\Controller\Adminhtml\Cart;

use Webkul\RewardSystem\Controller\Adminhtml\Cart as CartController;
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardcartFactory;

class Edit extends CartController
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
     * @var Webkul\RewardSystem\Model\RewardcartFactory
     */
    protected $_rewardcartFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     * @param RewardcartFactory                          $rewardcartFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        RewardcartFactory $rewardcartFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_rewardcartFactory = $rewardcartFactory;
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
            ->addBreadcrumb(__('Wallet System Credit Rule'), __('Wallet System Credit Rule'));
        return $resultPage;
    }
    /**
     * Execute method
     */
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('id');
        $model = $this->_rewardcartFactory
            ->create();
        if ($id) {
            $model->load($id);
            $flag = 1;
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('rewardsystem/cart/index');
            }
        }
        $data = $this->_session
                ->getFormData(true);

        if (isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        $this->_coreRegistry->register('reward_cartrewardData', $model);
        $resultPage = $this->_initAction();
        if ($flag==1 && $id) {
            $resultPage->addBreadcrumb(__('Edit Reward Cart Rule'), __('Edit Reward Cart Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Reward Cart Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Add Reward Cart Rule'), __('Add Reward Cart Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('Add Reward Cart Rule'));
        }
        return $resultPage;
    }
}
