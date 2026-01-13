<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Script;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Grouped extends Template implements ConfigInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_QuickOrder::script/grouped.phtml';

    /**
     * @var JsonSerializer
     */
    private $serializer;

    public function __construct(
        JsonSerializer $serializer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
    }

    public function getJsonConfig(): string
    {
        $jsonConfig = ['itemId' => $this->getItemId()];

        return $this->serializer->serialize($jsonConfig);
    }

    public function getItemId(): int
    {
        return (int) $this->getData('item_id');
    }

    public function setItemId(int $itemId): void
    {
        $this->setData('item_id', $itemId);
    }
}
