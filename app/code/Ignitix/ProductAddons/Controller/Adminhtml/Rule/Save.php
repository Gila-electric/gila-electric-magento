<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Controller\Adminhtml\Rule;

use Ignitix\ProductAddons\Model\RuleFactory;
use Ignitix\ProductAddons\Model\ResourceModel\Rule as RuleResource;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Ignitix_ProductAddons::rules';

    public function __construct(
        Action\Context $context,
        private readonly RuleFactory $ruleFactory,
        private readonly RuleResource $ruleResource,
        private readonly ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $post = (array)$this->getRequest()->getPostValue();

        // Your form uses dataScope="data" => fields are under $post['data']
        $data = (array)($post['data'] ?? []);
        if (!$data) {
            return $this->_redirect('*/*/');
        }

        $id = (int)($data['rule_id'] ?? 0);
        $model = $this->ruleFactory->create();

        if ($id) {
            $this->ruleResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $addonSku = trim((string)($data['addon_sku'] ?? ''));
        if ($addonSku === '') {
            $this->messageManager->addErrorMessage(__('Addon SKU is required.'));
            return $this->_redirect('*/*/edit', ['rule_id' => $id ?: null]);
        }

        try {
            $this->productRepository->get($addonSku);
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('Addon SKU not found: %1', $addonSku));
            return $this->_redirect('*/*/edit', ['rule_id' => $id ?: null]);
        }

        $model->setData('name', (string)($data['name'] ?? ''));
        $model->setData('is_active', (int)($data['is_active'] ?? 0));
        $model->setData('sort_order', (int)($data['sort_order'] ?? 0));
        $model->setData('addon_sku', $addonSku);

        $model->setData('target_product_ids', trim((string)($data['target_product_ids'] ?? '')));
        $model->setData('target_product_skus', trim((string)($data['target_product_skus'] ?? '')));
        $model->setData('target_category_ids', trim((string)($data['target_category_ids'] ?? '')));

        try {
            $this->ruleResource->save($model);
            $this->messageManager->addSuccessMessage(__('Rule saved.'));
            return $this->_redirect('*/*/');
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Save failed: %1', $e->getMessage()));
            return $this->_redirect('*/*/edit', ['rule_id' => $id ?: null]);
        }
    }
}