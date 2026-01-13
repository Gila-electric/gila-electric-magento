<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Ui\DataProvider\Rule;

use Ignitix\ProductAddons\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class FormDataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        private readonly CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $this->collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        $id = (int)$this->request->getParam('rule_id');

		// NEW RULE: return defaults so the UI form provider can bind and stop spinning
		if ($id <= 0) {
			$this->loadedData[0] = [
				'rule_id'   => 0, // IMPORTANT: do not return null
				'name'      => '',
				'is_active' => 1,
				'sort_order'=> 0,
				'addon_sku' => '',
				'target_product_ids'  => '',
				'target_product_skus' => '',
				'target_category_ids' => '',
			];
			return $this->loadedData;
		}

        $item = $this->collection->getItemById($id);
        if ($item) {
            $this->loadedData[$id] = $item->getData();
        } else {
            // If ID was provided but not found, still return a record to avoid spinner
            $this->loadedData[$id] = [
                'rule_id' => $id
            ];
        }

        return $this->loadedData;
    }
}