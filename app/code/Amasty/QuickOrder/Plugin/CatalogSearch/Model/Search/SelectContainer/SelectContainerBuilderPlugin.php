<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\CatalogSearch\Model\Search\SelectContainer;

use Amasty\QuickOrder\Api\SearchInterface;
use Amasty\QuickOrder\Model\Di\Wrapper;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerBuilder;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerFactory;
use Magento\Framework\Search\RequestInterface;

class SelectContainerBuilderPlugin
{
    /**
     * @var SelectContainerFactory|Wrapper
     */
    private $selectContainerFactory;

    public function __construct(Wrapper $selectContainerFactory)
    {
        $this->selectContainerFactory = $selectContainerFactory;
    }

    /**
     * @param SelectContainerBuilder $subject
     * @param SelectContainer $selectContainer
     * @param RequestInterface $request
     * @return SelectContainer
     */
    public function afterBuildByRequest(
        $subject,
        $selectContainer,
        RequestInterface $request
    ) {
        if ($request->getName() === SearchInterface::CONTAINER_NAME) {
            $data = [
                'select' => $selectContainer->getSelect(),
                'nonCustomAttributesFilters' => $selectContainer->getNonCustomAttributesFilters(),
                'customAttributesFilters' => $selectContainer->getCustomAttributesFilters(),
                'dimensions' => $selectContainer->getDimensions(),
                'isFullTextSearchRequired' => $selectContainer->isFullTextSearchRequired(),
                'isShowOutOfStockEnabled' => false,
                'usedIndex' => $selectContainer->getUsedIndex()
            ];
            $selectContainer = $this->selectContainerFactory->create($data);
        }

        return $selectContainer;
    }
}
