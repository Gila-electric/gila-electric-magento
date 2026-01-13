<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Grid;

use Magento\Framework\UrlInterface;

class MultipleConfigProcessor implements LayoutProcessorInterface
{
    public const UPLOAD_FILE_URL = 'amasty_quickorder/item_import/file';
    public const UPLOAD_LIST_URL = 'amasty_quickorder/item_import/multipleInput';
    public const MOVE_TEMP_URL = 'amasty_quickorder/item/moveTemp';
    public const SAMPLE_XML_URL = 'amasty_quickorder/file_sample/xml';
    public const SAMPLE_CSV_URL = 'amasty_quickorder/file_sample/csv';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function process($jsLayout): array
    {
        if (isset($jsLayout['components']['multiple']['config'])) {
            $jsLayout['components']['multiple']['config']['uploadFileUrl'] = $this->getUrl(static::UPLOAD_FILE_URL);
            $jsLayout['components']['multiple']['config']['uploadListUrl'] = $this->getUrl(static::UPLOAD_LIST_URL);
            $jsLayout['components']['multiple']['config']['acceptUrl'] = $this->getUrl(static::MOVE_TEMP_URL);
            $jsLayout['components']['multiple']['config']['sampleXmlUrl'] = $this->getUrl(static::SAMPLE_XML_URL);
            $jsLayout['components']['multiple']['config']['sampleCsvUrl'] = $this->getUrl(static::SAMPLE_CSV_URL);
        }

        return $jsLayout;
    }

    private function getUrl(string $route): string
    {
        return $this->urlBuilder->getUrl($route);
    }
}
