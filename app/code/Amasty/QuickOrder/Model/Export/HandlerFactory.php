<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export;

use Amasty\QuickOrder\Api\Export\HandlerInterface;
use Magento\Framework\ObjectManagerInterface;
use RuntimeException;

class HandlerFactory
{
    public const HANDLERS_PATH = '\Amasty\QuickOrder\Model\Export\\';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @return HandlerInterface
     */
    public function create(string $type): HandlerInterface
    {
        $className = static::HANDLERS_PATH . ucfirst($type);

        if (class_exists($className)) {
            $handler = $this->objectManager->get($className);
        } else {
            throw new RuntimeException('No export handle for type: ' . $type);
        }

        return $handler;
    }
}
