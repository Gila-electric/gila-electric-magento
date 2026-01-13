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
use Webkul\RewardSystem\Model\RewardproductSpecificFactory;
use Webkul\RewardSystem\Api\RewardproductSpecificRepositoryInterface;
use Webkul\RewardSystem\Api\Data\RewardproductSpecificInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class MassRewardPointSpecific extends ProductController
{
    /**
     * @var Webkul\RewardSystem\Model\RewardproductSpecificFactory
     */
    protected $_rewardProduct;

    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;
    
    /**
     * @var RewardproductSpecificInterfaceFactory
     */
    protected $_rewardproductSpecificInterface;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Action\Context                                       $context
     * @param RewardproductSpecificFactory                         $rewardProduct
     * @param RewardproductSpecificInterfaceFactory                $rewardproductSpecificInterface
     * @param \Webkul\RewardSystem\Helper\Data                     $rewardHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Json\DecoderInterface             $jsonDecoder
     */
    public function __construct(
        Action\Context $context,
        RewardproductSpecificFactory $rewardProduct,
        RewardproductSpecificInterfaceFactory $rewardproductSpecificInterface,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_rewardProduct = $rewardProduct;
        $this->_rewardproductSpecificInterface = $rewardproductSpecificInterface;
        $this->_rewardHelper = $rewardHelper;
        $this->_dateTime = $dateTime;
        $this->_jsonDecoder = $jsonDecoder;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * ConverToTz convert Datetime from one zone to another
     *
     * @param string $dateTime which we want to convert
     * @param string $toTz timezone in which we want to convert
     * @param string $fromTz timezone from which we want to convert
     */
    protected function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $newDate = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $newDate->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $newDate->format('H:i');
        return $dateTime;
    }

    /**
     * Execute method
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $params['start_time'] = $this->converToTz(
            $params['start_time'],
            // get default timezone of system (UTC)
            $this->timezone->getDefaultTimezone(),
            // get Config Timezone of current user
            $this->timezone->getConfigTimezone()
        );
        $params['end_time'] = $this->converToTz(
            $params['end_time'],
            // get default timezone of system (UTC)
            $this->timezone->getDefaultTimezone(),
            // get Config Timezone of current user
            $this->timezone->getConfigTimezone()
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('wk_productids', $params) && $params['wk_productids'] != '') {
            if (array_key_exists('rewardpoint', $params) &&
                array_key_exists('status', $params)) {
                $productIds = array_flip($this->_jsonDecoder->decode($params['wk_productids']));
                foreach ($productIds as $productId) {
                    $rewardProductModel = $this->_rewardProduct->create()->load($productId, 'product_id');
                    if ($rewardProductModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        $startTime = $params['start_time'];
                        $endTime = $params['end_time'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardProductModel->getPoints();
                            $startTime = $rewardProductModel->getStartTime();
                            $endTime = $rewardProductModel->getEndTime();
                        }
                        $data = [
                            'product_id' => $rewardProductModel->getProductId(),
                            'points' => $rewardPoint,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => $params['status'],
                            'entity_id' => $rewardProductModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'points' => $params['rewardpoint'],
                            'start_time' => $params['start_time'],
                            'end_time' => $params['end_time'],
                            'status' => $params['status']
                        ];
                    }
                    $this->_rewardHelper->setProductSpecificRewardData($data);
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
