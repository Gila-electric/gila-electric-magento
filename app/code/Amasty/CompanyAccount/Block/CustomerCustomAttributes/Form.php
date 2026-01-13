<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Block\CustomerCustomAttributes;

use Amasty\CompanyAccount\Model\Di\Wrapper;
use Magento\Framework\View\Element\Template;

class Form extends Wrapper
{
    public function __construct(
        Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        $name = '',
        array $data = []
    ) {
        parent::__construct(
            $context,
            $objectManagerInterface,
            // @phpstan-ignore-next-line
            \Magento\CustomerCustomAttributes\Block\Form::class,
            $data
        );
    }
}
