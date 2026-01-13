<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Controller\Adminhtml\Rule;

use Ignitix\ProductAddons\Model\RuleFactory;
use Ignitix\ProductAddons\Model\ResourceModel\Rule as RuleResource;
use Magento\Backend\App\Action;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Ignitix_ProductAddons::rules';

    public function __construct(
        Action\Context $context,
        private readonly RuleFactory $ruleFactory,
        private readonly RuleResource $ruleResource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('rule_id');
        if (!$id) {
            return $this->_redirect('*/*/');
        }

        $model = $this->ruleFactory->create();
        $this->ruleResource->load($model, $id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            return $this->_redirect('*/*/');
        }

        try {
            $this->ruleResource->delete($model);
            $this->messageManager->addSuccessMessage(__('Rule deleted.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Delete failed: %1', $e->getMessage()));
        }

        return $this->_redirect('*/*/');
    }
}