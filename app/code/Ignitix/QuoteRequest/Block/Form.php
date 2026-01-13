<?php
namespace Ignitix\QuoteRequest\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Form extends Template
{
    private const XML_PATH_ENABLED   = 'ignitix_quote_request/general/enabled';
    private const XML_PATH_RECIPIENT = 'ignitix_quote_request/general/recipient_email';

    public function isEnabled(): bool
    {
        return (bool)$this->_scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getRecipient(): string
    {
        return (string)$this->_scopeConfig->getValue(self::XML_PATH_RECIPIENT, ScopeInterface::SCOPE_STORE);
    }

    public function getPostUrl(): string
    {
        return $this->getUrl('gila-quote/index/post');
    }
}