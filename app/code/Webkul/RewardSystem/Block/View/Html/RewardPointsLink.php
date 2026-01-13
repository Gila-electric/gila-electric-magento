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
namespace Webkul\RewardSystem\Block\View\Html;

class RewardPointsLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\RewardSystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\RewardSystem\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }
        if ($this->helper->getCustomerId()) {
            $label = $this->escapeHtml($this->getLabel());
            $reward = $this->helper->getCurrentRewardOfCustomer($this->helper->getCustomerId());
            $imageUrl = $this->getViewFileUrl('Webkul_RewardSystem::images/reward.png');
            return '<li><a ' . $this->getLinkAttributes() . ' >'.
                '<img class="wk_rs_top_nav_logo" src="' . $imageUrl . '">' . $label . ' ' . $reward .
            '</a></li>';
        }
    }
}
