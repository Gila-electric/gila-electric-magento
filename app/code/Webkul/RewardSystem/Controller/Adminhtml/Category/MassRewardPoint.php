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

namespace Webkul\RewardSystem\Controller\Adminhtml\Category;

use Webkul\RewardSystem\Controller\Adminhtml\Category as CategoryController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardcategoryFactory;
use Webkul\RewardSystem\Api\RewardcategoryRepositoryInterface;
use Webkul\RewardSystem\Api\Data\RewardcategoryInterfaceFactory;

class MassRewardPoint extends CategoryController
{
    /**
     * @var Webkul\RewardSystem\Model\RewardproductFactory
     */
    protected $_rewardCategory;

    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardHelper;

    /**
     * @var RewardcategoryInterfaceFactory
     */
    protected $_rewardCategoryInterface;

     /**
      * @var \Magento\Framework\Json\DecoderInterface
      */
    protected $_jsonDecoder;

    /**
     * @param Action\Context                           $context
     * @param RewardcategoryFactory                    $rewardCategory
     * @param RewardcategoryInterfaceFactory           $rewardCategoryInterface
     * @param \Webkul\RewardSystem\Helper\Data         $rewardHelper
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        RewardcategoryFactory $rewardCategory,
        RewardcategoryInterfaceFactory $rewardCategoryInterface,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_rewardCategory = $rewardCategory;
        $this->_rewardCategoryInterface = $rewardCategoryInterface;
        $this->_rewardHelper = $rewardHelper;
        $this->_jsonDecoder = $jsonDecoder;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('reward_category', $params) && $params['reward_category'] != '') {
            if (array_key_exists('rewardpoint', $params) && array_key_exists('status', $params)) {
                $categoryIds = array_flip($this->_jsonDecoder->decode($params['reward_category']));
                foreach ($categoryIds as $categoryId) {
                    $rewardCategoryModel = $this->_rewardCategory->create()->load($categoryId, 'category_id');
                    if ($rewardCategoryModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardCategoryModel->getPoints();
                        }
                        $data = [
                            'category_id' => $rewardCategoryModel->getCategoryId(),
                            'points' => $rewardPoint,
                            'status' => $params['status'],
                            'entity_id' => $rewardCategoryModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'category_id' => $categoryId,
                            'points' => $params['rewardpoint'],
                            'status' => $params['status']
                        ];
                    }
                    $this->_rewardHelper->setCategoryRewardData($data);
                    $this->_rewardHelper->clearCache();
                }
                if ($params['rewardpoint'] == '') {
                    $this->messageManager->addSuccess(
                        __("Total of %1 category(s) reward status are updated", count($categoryIds))
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __("Total of %1 category(s) reward are updated", count($categoryIds))
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid points number to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select categories to set points.')
            );
        }
        return $resultRedirect->setPath('rewardsystem/category/index');
    }
}
