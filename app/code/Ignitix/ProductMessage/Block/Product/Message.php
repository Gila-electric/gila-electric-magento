<?php
namespace Ignitix\ProductMessage\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Message extends Template
{
    private const XML_PATH_ENABLED = 'ignitix_product_message/general/enabled';
    private const XML_PATH_MESSAGE = 'ignitix_product_message/general/message';

    public function isEnabled(): bool
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessage(): string
    {
        return (string)$this->_scopeConfig->getValue(
            self::XML_PATH_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled() || trim($this->getMessage()) === '') {
            return '';
        }
        return parent::_toHtml();
    }
}