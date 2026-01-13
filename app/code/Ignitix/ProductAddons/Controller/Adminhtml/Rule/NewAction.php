<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Ignitix_ProductAddons::rules';

    public function execute()
    {
        // Ensure rule_id exists in request so the UI form provider resolves data and stops spinning
        return $this->_redirect('*/*/edit', ['rule_id' => 0]);
    }
}