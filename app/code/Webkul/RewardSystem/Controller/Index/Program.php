<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Program extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\RewardSystem\Helper\Data $rewardHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardHelper = $rewardHelper;
        parent::__construct($context);
    }

    /**
     * Reward Program page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->rewardHelper->enableRewardSystem() && $this->rewardHelper->isRewardInfoEnabled()) {
            $rewardPageLabel = $this->rewardHelper->getRewardPageTitle();
            if (!$rewardPageLabel) {
                $rewardPageLabel = 'Reward Points';
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($rewardPageLabel));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('noroute');
        }
    }
}
