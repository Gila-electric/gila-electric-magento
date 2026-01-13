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

namespace Webkul\RewardSystem\Helper;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_TRANSACTION_MAIL = 'rewardsystem/email_settings/rewardsystem_transaction';
    public const XML_PATH_EXPIRY_MAIL = 'rewardsystem/email_settings/rewards_expiry';
    public const XML_PATH_INVITATION_MAIL = 'rewardsystem/email_settings/referral_invitation';
    /**
     * @var templateId
     */
    protected $_template;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
    }

    /**
     * Send Mail
     *
     * @param array  $receiverInfo
     * @param string $senderInfo
     * @param string $msg
     * @param int    $totalPoints
     * @param int    $storeId
     */
    public function sendMail($receiverInfo, $senderInfo, $msg, $totalPoints, $storeId = 0)
    {
        $emailTempVariables = [];
        $emailTempVariables['customername'] = $receiverInfo['name'];
        $emailTempVariables['transactiondetails'] = $msg;
        $emailTempVariables['remainingdetails'] = __(
            "Now total remaining reward points in your account are: %1",
            $totalPoints
        )->render();
        $this->_template = $this->getTemplateId(self::XML_PATH_TRANSACTION_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $storeId
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError('unable to send email.');
        }
        $this->_inlineTranslation->resume();
    }
    /**
     * Send Expire Email
     *
     * @param  mixed   $receiverInfo
     * @param  mixed   $senderInfo
     * @param  string  $msg
     * @param  float   $totalPoints
     * @param  integer $storeId
     * @return bool
     */
    public function sendExpireEmail($receiverInfo, $senderInfo, $msg, $totalPoints, $storeId = 0)
    {
        $emailTempVariables = [];
        $emailTempVariables['customername'] = $receiverInfo['name'];
        $emailTempVariables['transactiondetails'] = $msg;
        $this->_template = $this->getTemplateId(self::XML_PATH_EXPIRY_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $storeId
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError('unable to send email.');
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * Send Admin Mail
     *
     * @param array  $receiverInfo
     * @param string $senderInfo
     * @param string $adminMsg
     * @param int    $totalPoints
     * @param int    $storeId
     */
    public function sendAdminMail($receiverInfo, $senderInfo, $adminMsg, $totalPoints, $storeId = 0)
    {
        $emailTempVariables = [];
        $emailTempVariables['customername'] = 'Admin';
        $emailTempVariables['transactiondetails'] = $receiverInfo['name']." ".$adminMsg;
        $emailTempVariables['remainingdetails'] = __(
            "Now total remaining reward points in his/her account are: %1",
            $totalPoints
        )->render();
        $this->_template = $this->getTemplateId(self::XML_PATH_TRANSACTION_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $senderInfo,
            $storeId
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError('unable to send email.');
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * Send Invitation Mail
     *
     * @param array  $receiverInfo
     * @param string $senderInfo
     * @param string $referralUrl
     * @param int    $storeId
     */
    public function sendReferralInvitationMail($receiverInfo, $senderInfo, $referralUrl, $storeId = 0)
    {
        $emailTempVariables = [];
        $emailTempVariables['link'] = $referralUrl;
        $this->_template = $this->getTemplateId(self::XML_PATH_INVITATION_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $storeId
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            throw $e;
        }
        $this->_inlineTranslation->resume();
    }
    
    /**
     * Generate Template
     *
     * @param string $emailTemplateVariables
     * @param string $senderInfo
     * @param array  $receiverInfo
     * @param int    $storeId
     */
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId)
    {
        if (!$storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
    /**
     * Return template id.
     *
     * @param string $xmlPath
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
