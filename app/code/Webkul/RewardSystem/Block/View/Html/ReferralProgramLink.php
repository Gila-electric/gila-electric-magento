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

use Magento\Framework\App\DefaultPathInterface;

class ReferralProgramLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param DefaultPathInterface $defaultPath
     * @param \Webkul\RewardSystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        DefaultPathInterface $defaultPath,
        \Webkul\RewardSystem\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helper = $helper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper->isReferralEnabled()) {
            if (false != $this->getTemplate()) {
                return parent::_toHtml();
            }
    
            $highlight = '';
    
            if ($this->getIsHighlighted()) {
                $highlight = ' current';
            }
    
            if ($this->isCurrent()) {
                $html = '<li class="nav item current">';
                $html .= '<strong>'
                    . $this->escapeHtml(__($this->getLabel()))
                    . '</strong>';
                $html .= '</li>';
            } else {
                $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
                $html .= $this->getTitle()
                    ? ' title="' . $this->escapeHtml(__($this->getTitle())) . '"'
                    : '';
                $html .= $this->getAttributesHtml() . '>';
    
                if ($this->getIsHighlighted()) {
                    $html .= '<strong>';
                }
    
                $html .= $this->escapeHtml(__($this->getLabel()));
    
                if ($this->getIsHighlighted()) {
                    $html .= '</strong>';
                }
    
                $html .= '</a></li>';
            }
    
            return $html;
        }
        return '';
    }
}
