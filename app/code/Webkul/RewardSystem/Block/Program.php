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

class Program extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rewardHelper = $rewardHelper;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @inheritdoc
     */
    public function _prepareLayout()
    {
        // add Home breadcrumb
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'rewardpoints',
                ['label' => __('Reward Points')]
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare HTML content.
     *
     * @param mixed $value
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        return $this->filterProvider->getPageFilter()->filter($value);
    }

    /**
     * Get Helper Class
     */
    public function getHelperClass()
    {
        return $this->rewardHelper;
    }
}
