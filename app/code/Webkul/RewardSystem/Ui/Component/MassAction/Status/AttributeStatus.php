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

namespace Webkul\RewardSystem\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class AttributeStatus implements JsonSerializable
{
    /**
     * @var array
     */
    protected $statusOptions;
    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Constructor
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        if ($this->statusOptions === null) {
            $options = $this->getOptionArray();
            $this->prepareData();
            foreach ($options as $option) {
                $this->statusOptions[$option['value']] = [
                    'type' => $option['value'],
                    'label' => $option['label'],
                ];
                $this->statusOptions[$option['value']]['url'] = $this->urlBuilder->getUrl(
                    $this->urlPath,
                    [$this->paramName => $option['value']]
                );
                $this->statusOptions[$option['value']] = array_merge_recursive(
                    $this->statusOptions[$option['value']],
                    $this->additionalData
                );
            }
            $this->statusOptions = array_values($this->statusOptions);
        }
        return $this->statusOptions;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }

    /**
     * Get Option Array
     */
    public function getOptionArray()
    {
        $options = [
            [
                'value' => 1,
                'label' => 'Enabled'
            ], [
                'value' => 0,
                'label' => 'Disabled'
            ]
        ];
        return $options;
    }
}
