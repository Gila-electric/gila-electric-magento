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

namespace Webkul\RewardSystem\Controller\Adminhtml\Product;

use Webkul\RewardSystem\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardproductFactory;
use Webkul\RewardSystem\Api\RewardproductRepositoryInterface;
use Webkul\RewardSystem\Api\Data\RewardproductInterfaceFactory;

class MassRewardPoint extends ProductController
{
    /**
     * @var Webkul\RewardSystem\Model\RewardproductFactory
     */
    protected $_rewardProduct;

    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardHelper;
    
    /**
     * @var RewardproductInterfaceFactory
     */
    protected $_rewardProductInterface;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @param Action\Context                           $context
     * @param RewardproductFactory                     $rewardProduct
     * @param RewardproductInterfaceFactory            $rewardProductInterface
     * @param \Webkul\RewardSystem\Helper\Data         $rewardHelper
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        RewardproductFactory $rewardProduct,
        RewardproductInterfaceFactory $rewardProductInterface,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_rewardProduct = $rewardProduct;
        $this->_rewardProductInterface = $rewardProductInterface;
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
        $successCounter = 0;
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('wkproductids', $params) && $params['wkproductids'] != '') {
            if (array_key_exists('rewardpoint', $params) &&
                array_key_exists('status', $params)) {
                $productIds = array_flip($this->_jsonDecoder->decode($params['wkproductids']));
                foreach ($productIds as $productId) {
                    $rewardProductModel = $this->_rewardProduct->create()->load($productId, 'product_id');
                    if ($rewardProductModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardProductModel->getPoints();
                        }
                        $data = [
                            'product_id' => $rewardProductModel->getProductId(),
                            'points' => $rewardPoint,
                            'status' => $params['status'],
                            'entity_id' => $rewardProductModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'points' => $params['rewardpoint'],
                            'status' => $params['status']
                        ];
                    }
                    $this->_rewardHelper->setProductRewardData($data);
                    $this->_rewardHelper->clearCache();
                }
                if ($params['rewardpoint'] == '') {
                    $this->messageManager->addSuccess(
                        __("Total of %1 product(s) reward status are updated", count($productIds))
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __("Total of %1 product(s) reward are updated", count($productIds))
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid reward points number to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select products to set points.')
            );
        }
        return $resultRedirect->setPath('rewardsystem/product/index');
    }
}
