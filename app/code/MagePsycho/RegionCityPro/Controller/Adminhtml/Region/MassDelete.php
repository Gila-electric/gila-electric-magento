<?php

namespace MagePsycho\RegionCityPro\Controller\Adminhtml\Region;

use Magento\Framework\Controller\ResultFactory;
use Magento\Directory\Model\Region;
use MagePsycho\RegionCityPro\Controller\Adminhtml\Region as RegionController;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassDelete extends RegionController
{
    public function execute()
    {
        $regionIds = $this->getRequest()->getParam('selected');
        if (! is_array($regionIds)) {
            $this->messageManager->addErrorMessage(__('Please select one or more items.'));
        } else {
            try {
                foreach ($regionIds as $regionId) {
                    /** @var Region $region */
                    $region = $this->regionFactory->create()->load($regionId);
                    $region->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were deleted.', count($regionIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
