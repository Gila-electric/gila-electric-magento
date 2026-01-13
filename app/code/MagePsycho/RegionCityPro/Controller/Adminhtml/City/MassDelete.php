<?php

namespace MagePsycho\RegionCityPro\Controller\Adminhtml\City;

use Magento\Framework\Controller\ResultFactory;
use MagePsycho\RegionCityPro\Model\City;
use MagePsycho\RegionCityPro\Controller\Adminhtml\City as CityController;

class MassDelete extends CityController
{
    public function execute()
    {
        $cityIds = $this->getRequest()->getParam('selected');
        if (!is_array($cityIds)) {
            $this->messageManager->addErrorMessage(__('Please select one or more items.'));
        } else {
            try {
                foreach ($cityIds as $cityId) {
                    /** @var City $city */
                    $city = $this->cityFactory->create()->load($cityId);
                    $city->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were deleted.', count($cityIds))
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
