<?php
namespace Ignitix\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class Index extends Action
{
    private const XML_PATH_ENABLED = 'ignitix_quote_request/general/enabled';

    public function execute()
    {
        $enabled = (bool)$this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);

        if (!$enabled) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Request a Quote'));
        return $resultPage;
    }
}