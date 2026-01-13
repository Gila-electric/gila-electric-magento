<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Controller\Adminhtml\Rule;

use Ignitix\ProductAddons\Model\RuleFactory;
use Ignitix\ProductAddons\Model\ResourceModel\Rule as RuleResource;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\PageFactory;
use Magento\Framework\Registry;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Ignitix_ProductAddons::rules';

    public function __construct(
        Action\Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly RuleFactory $ruleFactory,
        private readonly RuleResource $ruleResource,
        private readonly Registry $registry
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('rule_id');
        $model = $this->ruleFactory->create();

        if ($id) {
            $this->ruleResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $this->registry->register('ignitix_productaddons_rule', $model);

        $page = $this->resultPageFactory->create();

        // DO NOT call setActiveMenu() here (menu block is missing -> hard crash)

        $page->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Rule') : __('New Rule'));
        return $page;
    }
}