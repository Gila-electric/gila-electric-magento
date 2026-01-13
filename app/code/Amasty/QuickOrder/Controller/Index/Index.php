<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Index;

use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Model\GetIsAvailable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    public const NOROUTE = 'noroute';

    /**
     * @var GetIsAvailable
     */
    private $getIsAvailable;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        GetIsAvailable $getIsAvailable,
        Context $context
    ) {
        parent::__construct($context);
        $this->getIsAvailable = $getIsAvailable;
        $this->configProvider = $configProvider;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if ($this->isActionAvailable()) {
            /** @var Page $result */
            $result = $this->createResult(ResultFactory::TYPE_PAGE);
            $this->setPageTitle($result);
        } else {
            $result = $this->createResult(ResultFactory::TYPE_FORWARD)->forward(static::NOROUTE);
        }

        return $result;
    }

    /**
     * @param Page $page
     */
    private function setPageTitle(Page $page)
    {
        if (($titleBlock = $page->getLayout()->getBlock('page.main.title'))
            && method_exists($titleBlock, 'setPageTitle')
        ) {
            $titleBlock->setPageTitle($this->configProvider->getLabel());
        }
    }

    /**
     * @return bool
     */
    private function isActionAvailable(): bool
    {
        return $this->getRequest()->isGet() && $this->getIsAvailable->execute();
    }

    /**
     * @param string $type
     * @return ResultInterface
     */
    private function createResult(string $type)
    {
        return $this->resultFactory->create($type);
    }
}
