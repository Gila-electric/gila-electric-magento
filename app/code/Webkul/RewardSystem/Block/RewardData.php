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

namespace Webkul\RewardSystem\Block;

use Magento\Framework\Json\Helper\Data;

class RewardData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $_rewardHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\RewardSystem\Helper\Data                 $rewardHelper
     * @param Data                                             $jsonData
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        Data $jsonData,
        array $data = []
    ) {
        $this->_rewardHelper = $rewardHelper;
        $this->jsonData = $jsonData;
        parent::__construct($context, $data);
    }

     /**
      * Get Helper Class
      */
    public function getHelperClass()
    {
        return $this->_rewardHelper;
    }

    /**
     * Get JSON helper
     */
    public function getJsonHelper()
    {
        return $this->jsonData;
    }
}
