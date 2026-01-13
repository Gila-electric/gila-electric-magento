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
namespace Webkul\RewardSystem\Controller\Adminhtml\Support;

use Magento\Backend\App\Action;

class Services extends Action
{
    /**
     * Support Services Link.
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl("https://webkul.com/magento-development/");
        return $resultRedirect;
    }
}
