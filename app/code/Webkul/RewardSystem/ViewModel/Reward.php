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

namespace Webkul\RewardSystem\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\RewardSystem\Helper\Data as HelperData;
use Webkul\RewardSystem\Helper\Chart as HelperChart;

/**
 * View Model for Reward System
 */
class Reward implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var HelperChart
     */
    protected $helperChart;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     * @param HelperChart $helperChart
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperData $helperData,
        HelperChart $helperChart
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->helperChart = $helperChart;
    }

    /**
     * Get Reward Data helper
     *
     * @return \Webkul\RewardSystem\Helper\Data
     */
    public function getRewardDataHelper()
    {
        return $this->helperData;
    }

    /**
     * Get Reward Data helper
     *
     * @return \Webkul\RewardSystem\Helper\Data
     */
    public function getRewardChartHelper()
    {
        return $this->helperChart;
    }
}
