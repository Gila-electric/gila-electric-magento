<?php
namespace Ignitix\FixArea\Plugin\FrontController;

use Magento\Framework\App\Area;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class EnsureArea
{
    public function __construct(private State $state) {}

    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request)
    {
        try { $this->state->getAreaCode(); }
        catch (LocalizedException $e) { $this->state->setAreaCode(Area::AREA_FRONTEND); }
        return [$request];
    }
}