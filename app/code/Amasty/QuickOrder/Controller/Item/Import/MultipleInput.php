<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Import;

use Amasty\QuickOrder\Model\Import\ArrayHandler;
use Amasty\QuickOrder\Model\Import\ImportHandler;
use Exception;
use Magento\Framework\Exception\LocalizedException;

class MultipleInput extends AbstractAction
{
    public const INPUT_NAME = 'multiple_sku';

    /**
     * @var int
     */
    private $totalQty = 1;

    /**
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function importAction(): array
    {
        if ($importArray = $this->getArrayForImport()) {
            return $this->getArrayHandler()->import($importArray);
        } else {
            throw new LocalizedException(__('Input with products was not provided.'));
        }
    }

    /**
     * @return array|null
     */
    private function getArrayForImport()
    {
        $data = $this->getRequest()->getParam(static::INPUT_NAME, null);
        if ($data) {
            $data = array_filter(array_map('trim', explode(PHP_EOL, $data)));

            $data = array_map(
                function ($row) {
                    $row = explode(',', $row);
                    $row[ImportHandler::SKU_FIELD] = $row[0] ?? '';
                    unset($row[0]);

                    $qty = 1;
                    if (isset($row[1])) {
                        $possibleQty = explode(':', $row[1]);
                        if ((count($possibleQty) === 1 && (float)$possibleQty) || $possibleQty[0] === 'qty') {
                            $qty = (float)end($possibleQty);
                            unset($row[1]);
                        }
                    }
                    $row[ImportHandler::QTY_FIELD] = $qty;

                    return $row;
                },
                $data
            );
            $this->totalQty = count($data);
        }

        return $data;
    }

    /**
     * @return ArrayHandler|null
     * @throws Exception
     */
    private function getArrayHandler()
    {
        return $this->getData('arrayHandler');
    }

    /**
     * @return int
     */
    public function calculateTotalQty(): int
    {
        return $this->totalQty;
    }
}
