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

namespace Webkul\RewardSystem\Plugin\Block\Widget;

use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;

class Dob
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param RewardSystemHelper $helper
     */
    public function __construct(
        RewardSystemHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After Is Enabled
     *
     * @param \Magento\Customer\Block\Widget\Dob $subject
     * @param array $result
     */
    public function afterIsEnabled(
        \Magento\Customer\Block\Widget\Dob $subject,
        $result
    ) {
        $helper = $this->helper;
        $enableRewardSystem = $helper->enableRewardSystem();
        if ($helper->getConfigData('reward_on_birthday') && $enableRewardSystem) {
            return true;
        }

        return $result;
    }

    /**
     * After Get Date Format
     */
    public function afterGetDateFormat()
    {
        $escapedDateFormat = 'M/dd/Y';

        return $escapedDateFormat;
    }
}
