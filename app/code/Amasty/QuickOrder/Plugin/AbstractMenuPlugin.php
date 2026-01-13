<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin;

use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Model\GetIsAvailable;
use Amasty\QuickOrder\Model\Source\DisplayPlace;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Psr\Log\LoggerInterface;

abstract class AbstractMenuPlugin
{
    public const CACHE_TAG = 'quickorder_customer_group';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetIsAvailable
     */
    private $getIsAvailable;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetIsAvailable $getIsAvailable,
        ConfigProvider $configProvider,
        UrlInterface $urlBuilder,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
        $this->getIsAvailable = $getIsAvailable;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * @param AbstractBlock $block
     * @param array $cacheKeyInfo
     * @return array
     */
    public function afterGetCacheKeyInfo(AbstractBlock $block, $cacheKeyInfo)
    {
        try {
            $cacheKeyInfo = array_merge(
                $cacheKeyInfo,
                [self::CACHE_TAG . '_' . $this->customerSession->getCustomerGroupId()]
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return $cacheKeyInfo;
    }

    /**
     * @param Node $parentNode
     */
    protected function addQuickOrderNode(Node $parentNode)
    {
        $parentNode->addChild(new Node(
            $this->getNodeAsArray(),
            'id',
            $parentNode->getTree()
        ));
    }

    /**
     * @return bool
     */
    protected function isShowLink(): bool
    {
        return $this->configProvider->isDisplayLInkIn(DisplayPlace::TOP_MENU)
            && $this->getIsAvailable->execute();
    }

    /**
     * @return array
     */
    protected function getNodeAsArray(): array
    {
        $url = $this->urlBuilder->getUrl($this->configProvider->getUrlKey());
        return [
            'name' => $this->configProvider->getLabel(),
            'id' => 'amasty_quickorder_link',
            'url' => $url,
            'has_active' => false,
            'is_active' => $url == $this->urlBuilder->getCurrentUrl()
        ];
    }

    /**
     * @return string
     */
    protected function getNodeHtml(): string
    {
        $data = $this->getNodeAsArray();

        return '<li class="nav-item nav-item--brand level0 level-top">
                    <a class="level-top" href="' . $data['url'] . '"><span>' . $data['name'] . '</span></a>
                </li>';
    }
}
