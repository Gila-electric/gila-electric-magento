<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
declare(strict_types=1);

namespace Webkul\RewardSystem\Controller\Adminhtml\Reports\Chart;

use Magento\Backend\App\Action\Context;
use Webkul\RewardSystem\Controller\Adminhtml\Reports;
use Webkul\RewardSystem\Model\Reports\Period;
use Webkul\RewardSystem\Helper\Chart as HelperChart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Refresh extends Reports implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var HelperChart
     */
    private $helperChart;

    /**
     * @var Period
     */
    private $period;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Period $period
     * @param HelperChart $helperChart
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Period $period,
        HelperChart $helperChart
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->period = $period;
        $this->helperChart = $helperChart;
    }

    /**
     * Get chart data
     *
     * @return Json
     */
    public function execute(): Json
    {
        try {
            $data = [
                'data' => $this->helperChart->getRewardInfo(
                    $this->_request->getParam('period'),
                    $this->_request->getParam('website'),
                    $this->_request->getParam('custgroup')
                ),
                'success' => true
            ];
        } catch (\Exception $e) {
            $data = [
                'success' => false
            ];
        }

        return $this->resultJsonFactory->create()
            ->setData($data);
    }
}
