<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category implements ArrayInterface
{
    public const NONE = '';
    public const SYSTEM_CATEGORY_ID = 1;
    public const ROOT_LEVEL = 1;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getChildren(self::SYSTEM_CATEGORY_ID, self::ROOT_LEVEL);
    }

    private function getChildren(int $parentCategoryId, int $level): array
    {
        $options[self::NONE] = __('None');

        foreach ($this->getCategoryCollection($parentCategoryId, $level) as $category) {
            if ($category->getLevel() > self::ROOT_LEVEL) {
                $options[$category->getId()] =
                    str_repeat(". ", max(0, ($category->getLevel() - 1) * 3)) . $category->getName();
            }
            if ($category->hasChildren()) {
                $options = array_replace($options, $this->getChildren(
                    (int) $category->getId(),
                    $category->getLevel() + 1
                ));
            }
        }

        return $options;
    }

    private function getCategoryCollection(int $parentCategoryId, int $level): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('level', $level);
        $collection->addAttributeToFilter('parent_id', $parentCategoryId);
        $collection->addAttributeToFilter('is_active', 1);
        $collection->setOrder('position', 'asc');

        return $collection;
    }
}
