<?php

namespace Installment\Installment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

 


class Data extends AbstractHelper
{
 
    protected $scopeConfig;

        public function __construct(
            ScopeConfigInterface $scopeConfig
        ) {
            $this->scopeConfig = $scopeConfig;
        }


        public function getConfigLabel($config_path)
        {
            return $this->scopeConfig->getLabel($config_path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        
        public function getConfig($config_path)
        {
            return $this->scopeConfig->getValue($config_path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }


        
}


