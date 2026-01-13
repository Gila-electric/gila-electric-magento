<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch;

abstract class AdditionalFieldMapper
{
    public const ES_DATA_TYPE_STRING = 'string';
    public const ES_DATA_TYPE_FLOAT = 'float';
    public const ES_DATA_TYPE_INT = 'integer';
    public const ES_DATA_TYPE_DATE = 'date';

    /**
     * @var array
     */
    private $fields = [];

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function updateFields(array $fields): array
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            if (empty($fieldName)) {
                continue;
            }
            if ($this->isValidFieldType($fieldType)) {
                $fields[$fieldName] = ['type' => $fieldType];
            }
        }

        return $fields;
    }

    /**
     * @param $fieldType
     * @return bool
     */
    private function isValidFieldType($fieldType)
    {
        switch ($fieldType) {
            case self::ES_DATA_TYPE_STRING:
            case self::ES_DATA_TYPE_DATE:
            case self::ES_DATA_TYPE_INT:
            case self::ES_DATA_TYPE_FLOAT:
                break;
            default:
                $fieldType = false;
                break;
        }

        return $fieldType;
    }
}
