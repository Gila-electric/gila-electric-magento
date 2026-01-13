<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Source;

use Amasty\QuickOrder\Model\Config\Utils;
use Amasty\QuickOrder\Model\ConfigProvider;
use Magento\Config\Model\Config as BackendConfig;
use Magento\Framework\Data\OptionSourceInterface;

class FileGroup implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private $options;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var Utils
     */
    private $configUtils;

    /**
     * @var BackendConfig
     */
    private $backendConfig;

    /**
     * @var array|null
     */
    private $disabledCustomerGroups;

    public function __construct(
        Group $group,
        Utils $configUtils,
        BackendConfig $backendConfig
    ) {
        $this->group = $group;
        $this->configUtils = $configUtils;
        $this->backendConfig = $backendConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = array_filter($this->group->toOptionArray(), function ($group) {
                return !in_array((int) $group['value'], $this->getDisabledCustomerGroups());
            });
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getDisabledCustomerGroups(): array
    {
        if ($this->disabledCustomerGroups === null) {
            $this->disabledCustomerGroups = $this->configUtils->parseMultiselect(
                (string) $this->backendConfig->getConfigDataValue(
                    ConfigProvider::PATH_PREFIX . ConfigProvider::DISABLED_CUSTOMER_GROUPS_PATH
                )
            );
        }

        return $this->disabledCustomerGroups;
    }
}
