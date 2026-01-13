<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\View\Page\Config;

use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;

class Renderer
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Base\Model\MagentoVersion $magentoVersion
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * Add our css file if less functionality is missing
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     * @return array
     */
    public function beforeRenderAssets(
        MagentoRenderer $subject,
        $resultGroups = []
    ) {
        $version = $this->magentoVersion->get();
        $version = str_replace(['-develop', 'dev-'], '', $version);

        if (version_compare($version, '2.3.0', '<')
            && ($this->request->getFullActionName() == 'amasty_quickorder_index_index')
        ) {
            $this->config->addPageAsset('Magento_Swatches::css/swatches.css');
        }

        return [$resultGroups];
    }
}
