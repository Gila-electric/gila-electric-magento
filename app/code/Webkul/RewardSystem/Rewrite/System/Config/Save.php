<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Rewrite\System\Config;

class Save extends \Magento\Config\Controller\Adminhtml\System\Config\Save
{
    /**
     * Save configuration
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $flag = true;
        $params = $this->getRequest()->getParams();
        if (isset($params['section']) && $params['section'] == 'rewardsystem'
            && empty($params['store']) && empty($params['website'])
        ) {
            $paramsData = $params['groups']['general_settings']['fields'];
            $maxRewardAssign = $paramsData['max_reward_assign'];
            $registrationReward = isset($paramsData['registration_reward']) ? $paramsData['registration_reward'] : 0;
            $reviewReward = isset($paramsData['review_reward']) ? $paramsData['review_reward'] : 0;
            $birthdayReward = isset($paramsData['birthday_reward']) ? $paramsData['birthday_reward'] : 0;
            if ($maxRewardAssign < $paramsData['max_reward_used']) {
                $flag = false;
                $this->messageManager->addError(
                    __('"Maximum Reward Points can be Used By a Customer" can not be greater than
                     "Maximum Reward Points can Assign to a Customer"')
                );
            }
            if ($maxRewardAssign < $registrationReward) {
                $flag = false;
                $this->messageManager->addError(
                    __('"Reward Points On Registration" can not be greater than
                     "Maximum Reward Points can Assign to a Customer"')
                );
            }
            if ($maxRewardAssign < $reviewReward) {
                $flag = false;
                $this->messageManager->addError(
                    __('"Reward Points On Product Review" can not be greater than
                     "Maximum Reward Points can Assign to a Customer"')
                );
            }
            if ($maxRewardAssign < $birthdayReward) {
                $flag = false;
                $this->messageManager->addError(
                    __('"Reward Points On Birthday" can not be greater than
                     "Maximum Reward Points can Assign to a Customer"')
                );
            }
        }
        if ($flag) {
            try {
                // custom save logic
                $this->_saveSection();
                $section = $this->getRequest()->getParam('section');
                $website = $this->getRequest()->getParam('website');
                $store = $this->getRequest()->getParam('store');
                $configData = [
                    'section' => $section,
                    'website' => $website,
                    'store' => $store,
                    'groups' => $this->_getGroupsForSave(),
                ];
                $configData = $this->filterNodes($configData);

                /** @var \Magento\Config\Model\Config $configModel */
                $configModel = $this->_configFactory->create(['data' => $configData]);
                $configModel->save();
                $this->_eventManager->dispatch(
                    'admin_system_config_save',
                    ['configData' => $configData, 'request' => $this->getRequest()]
                );
                $this->messageManager->addSuccess(__('You saved the configuration.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages = explode("\n", $e->getMessage());
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
                );
            }

            $this->_saveState($this->getRequest()->getPost('config_state'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true
            ]
        );
    }

    /**
     * Filter paths that are not defined.
     *
     * @param string $prefix Path prefix
     * @param array $groups Groups data.
     * @param string[] $systemXmlConfig Defined paths.
     * @return array Filtered groups.
     */
    private function filterPaths(string $prefix, array $groups, array $systemXmlConfig): array
    {
        $flippedXmlConfig = array_flip($systemXmlConfig);
        $filtered = [];
        foreach ($groups as $groupName => $childPaths) {
            //When group accepts arbitrary fields and clones them we allow it
            $group = $this->_configStructure->getElement($prefix .'/' .$groupName);
            if (array_key_exists('clone_fields', $group->getData()) && $group->getData()['clone_fields']) {
                $filtered[$groupName] = $childPaths;
                continue;
            }

            $filtered[$groupName] = ['fields' => [], 'groups' => []];
            //Processing fields
            if (array_key_exists('fields', $childPaths)) {
                foreach ($childPaths['fields'] as $field => $fieldData) {
                    //Constructing config path for the $field
                    $path = $prefix .'/' .$groupName .'/' .$field;
                    $element = $this->_configStructure->getElement($path);
                    if ($element
                        && ($elementData = $element->getData())
                        && array_key_exists('config_path', $elementData)
                    ) {
                        $path = $elementData['config_path'];
                    }
                    //Checking whether it exists in system.xml
                    if (array_key_exists($path, $flippedXmlConfig)) {
                        $filtered[$groupName]['fields'][$field] = $fieldData;
                    }
                }
            }
            //Recursively filtering this group's groups.
            if (array_key_exists('groups', $childPaths) && $childPaths['groups']) {
                $filteredGroups = $this->filterPaths(
                    $prefix .'/' .$groupName,
                    $childPaths['groups'],
                    $systemXmlConfig
                );
                if ($filteredGroups) {
                    $filtered[$groupName]['groups'] = $filteredGroups;
                }
            }

            $filtered[$groupName] = array_filter($filtered[$groupName]);
        }

        return array_filter($filtered);
    }

    /**
     * Filters nodes by checking whether they exist in system.xml.
     *
     * @param array $configData
     * @return array
     */
    private function filterNodes(array $configData): array
    {
        if (!empty($configData['groups'])) {
            $systemXmlPathsFromKeys = array_keys($this->_configStructure->getFieldPaths());
            $systemXmlPathsFromValues = array_reduce(
                array_values($this->_configStructure->getFieldPaths()),
                'array_merge',
                []
            );
            //Full list of paths defined in system.xml
            $systemXmlConfig = array_merge($systemXmlPathsFromKeys, $systemXmlPathsFromValues);

            $configData['groups'] = $this->filterPaths($configData['section'], $configData['groups'], $systemXmlConfig);
        }

        return $configData;
    }
}
