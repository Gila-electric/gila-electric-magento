<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Ignitix_ProductAddons::rules';

    public function __construct(
        Action\Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();

        // DO NOT call setActiveMenu() here (menu block is missing -> hard crash)

        $page->getConfig()->getTitle()->prepend(__('Product Add-ons Rules'));
        return $page;
    }
}