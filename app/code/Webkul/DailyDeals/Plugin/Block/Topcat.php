<?php
namespace Webkul\DailyDeals\Plugin\Block;
 
use Magento\Framework\Data\Tree\NodeFactory;
 
class Topcat
{

    /**
     * @var NodeFactory
     */
    public $nodeFactory;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $urlInterface;
    
    /**
     * Constructor
     *
     * @param NodeFactory $nodeFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        NodeFactory $nodeFactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->urlInterface = $urlInterface;
    }
    
    /**
     * Before Get html
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param integer $limit
     * @return void
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        $node = $this->nodeFactory->create(
            [
                'data' => $this->getNodeAsArray(),
                'idField' => 'id',
                'tree' => $subject->getMenu()->getTree()
            ]
        );
        $subject->getMenu()->addChild($node);
    }
    
    /**
     * Get Nodes As Array
     *
     * @return void
     */
    public function getNodeAsArray()
    {
        return [
            'name' => __('Daily Deals'),
            'id' => 'daily-deals-menu',
            'url' => $this->urlInterface->getUrl('dailydeals'),
            'has_active' => false,
            'is_active' => $this->isActive()
        ];
    }
    
    /**
     * Is Active
     *
     * @return boolean
     */
    private function isActive()
    {
        $activeUrls = 'dailydeals';
        $currentUrl = $this->urlInterface->getCurrentUrl();
        if (strpos($currentUrl, $activeUrls) !== false) {
            return true;
        }
        return false;
    }
}
