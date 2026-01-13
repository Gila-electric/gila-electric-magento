<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class RuleActions extends Column
{
    private const URL_PATH_EDIT   = 'ignitix_productaddons/rule/edit';
    private const URL_PATH_DELETE = 'ignitix_productaddons/rule/delete';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $id = (int)($item['rule_id'] ?? 0);
            if (!$id) {
                continue;
            }

            $name = $this->getData('name');

            $item[$name]['edit'] = [
                'href'  => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['rule_id' => $id]),
                'label' => __('Edit'),
            ];

            $item[$name]['delete'] = [
                'href'    => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['rule_id' => $id]),
                'label'   => __('Delete'),
                'confirm' => [
                    'title'   => __('Delete Rule'),
                    'message' => __('Are you sure you want to delete this rule?'),
                ],
                'post' => true,
            ];
        }

        return $dataSource;
    }
}