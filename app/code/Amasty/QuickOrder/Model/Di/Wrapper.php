<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Di;

use Magento\Framework\ObjectManagerInterface;

class Wrapper
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManagerInterface;

    /**
     * @var string
     */
    private $name;

    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        $name = ''
    ) {
        $this->objectManagerInterface = $objectManagerInterface;
        $this->name = $name;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        $result = false;
        if ($this->name) {
            $object = $this->objectManagerInterface->create($this->name);

            // @codingStandardsIgnoreLine
            $result = call_user_func_array([$object, $name], $arguments);
        }

        return $result;
    }
}
