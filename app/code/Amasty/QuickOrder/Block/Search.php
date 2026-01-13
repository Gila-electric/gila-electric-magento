<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block;

use Magento\Framework\View\Element\Template;
use Magento\Search\Helper\Data as SearchHelper;

class Search extends Template
{
    /**
     * @var SearchHelper
     */
    private $searchHelper;

    public function __construct(
        SearchHelper $searchHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchHelper = $searchHelper;
    }

    /**
     * @return false|string
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->updateSearchConfig($this->jsLayout);

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateSearchConfig(array $jsLayout): array
    {
        if (isset($jsLayout['components']['search']['config'])) {
            $jsLayout['components']['search']['config']['minChars'] = $this->searchHelper->getMinQueryLength();
            $jsLayout['components']['search']['config']['maxChars'] = $this->searchHelper->getMaxQueryLength();
        }

        return $jsLayout;
    }
}
