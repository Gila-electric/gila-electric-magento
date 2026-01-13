<?php

namespace Accept\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */


    protected $method;
    protected $scopeConfig;
    protected $storeManager;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Session                         $customerSession
     * @param Url                             $urlBuilder
     */
    /**
     * @param ScopeConfig           $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfig $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }


    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        return [
            'payment' => [
                ValuAccept::CODE         => [
                    'logo'         => $this->getLogo(ValuAccept::CODE),
                    'instructions' => $this->getInstructions(ValuAccept::CODE)
                ],
                Getgo::CODE        => [
                    'logo'         => $this->getLogo(Getgo::CODE),
                    'instructions' => $this->getInstructions(Getgo::CODE)
                ],
                Souhoola::CODE     => [
                    'logo'         => $this->getLogo(Souhoola::CODE),
                    'instructions' => $this->getInstructions(Souhoola::CODE)
                ],
                Sympl::CODE        => [
                    'logo'         => $this->getLogo(Sympl::CODE),
                    'instructions' => $this->getInstructions(Sympl::CODE)
                ],
                premium::CODE      => [
                    'logo'         => $this->getLogo(premium::CODE),
                    'instructions' => $this->getInstructions(premium::CODE)
                ],
                Online::CODE       => [
                    'logo'         => $this->getLogo(Online::CODE),
                    'instructions' => $this->getInstructions(Online::CODE)
                ],
                Wallet::CODE       => [
                    'logo'         => $this->getLogo(Wallet::CODE),
                    'instructions' => $this->getInstructions(Wallet::CODE)
                ],
                installments::CODE => [
                    'logo'         => $this->getLogo(installments::CODE),
                    'instructions' => $this->getInstructions(installments::CODE)
                ],
                ios::CODE => [
                    'logo'         => $this->getLogo(ios::CODE),
                    'instructions' => $this->getInstructions(ios::CODE)
                ],
                Kiosk::CODE => [
                    'logo'         => $this->getLogo(Kiosk::CODE),
                    'instructions' => $this->getInstructions(Kiosk::CODE)
                ],
                Forsa::CODE => [
                    'logo'         => $this->getLogo(Forsa::CODE),
                    'instructions' => $this->getInstructions(Forsa::CODE)
                ],
                Contact::CODE => [
                    'logo'         => $this->getLogo(Contact::CODE)
                ],
                Aman::CODE => [
                    'logo'         => $this->getLogo(Aman::CODE)
                ]
            ],
        ];
    }

    /**
     * @param $paymentCode
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLogo($paymentCode)
    {
        $mediaPath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $logo = $this->getConfigData($paymentCode, 'logo');
        return $logo ? $mediaPath . 'paymentslogo/' . $logo : '';
    }

    /**
     * @param $paymentCode
     *
     * @return mixed
     */
    private function getInstructions($paymentCode)
    {
        return $this->getConfigData($paymentCode, 'instructions');
    }

    /**
     * @param $paymentCode
     * @param $field
     *
     * @return mixed
     */
    private function getConfigData($paymentCode, $field)
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('payment/' . $paymentCode . '/' . $field, $storeScope);
    }
}
