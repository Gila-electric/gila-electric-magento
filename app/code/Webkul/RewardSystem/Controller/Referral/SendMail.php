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

namespace Webkul\RewardSystem\Controller\Referral;

use Magento\Framework\App\Action\Context;

class SendMail extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     */
    public function __construct(
        Context $context,
        \Webkul\RewardSystem\Helper\Data $rewardHelper
    ) {
        $this->rewardHelper = $rewardHelper;
        parent::__construct($context);
    }
    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->rewardHelper->enableRewardSystem() && $this->rewardHelper->isReferralEnabled()) {
            $params = $this->getRequest()->getParams();
            $this->rewardHelper->processReferralInvitation($params);
            $this->messageManager->addSuccess('Invitation sent successfully.');
            return $resultRedirect->setPath('rewardsystem/referral/');
        } else {
            return $resultRedirect->setPath('customer/account/');
        }
    }
}
