<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item;

use Amasty\QuickOrder\Model\Session;

class IdGenerator
{
    /**
     * @var int
     */
    private $lastId = 0;

    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->init();
    }

    private function init()
    {
        $this->lastId = $this->session->getLastId();
    }

    private function updateLastId()
    {
        $this->lastId++;
        $this->session->setLastId($this->lastId);
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        $this->updateLastId();
        return $this->lastId;
    }
}
