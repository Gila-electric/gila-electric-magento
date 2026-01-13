<?php
namespace Ignitix\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder; // this is our class via preference
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Post extends Action
{
    private const XML_PATH_ENABLED   = 'ignitix_quote_request/general/enabled';
    private const XML_PATH_RECIPIENT = 'ignitix_quote_request/general/recipient_email';

    private $formKeyValidator;
    private $redirectFactory;
    private $filesystem;
    private $uploaderFactory;
    private $transportBuilder;
    private $storeManager;
    private $inlineTranslation;
    private $scopeConfig;

    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        RedirectFactory $redirectFactory,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->formKeyValidator  = $formKeyValidator;
        $this->redirectFactory   = $redirectFactory;
        $this->filesystem        = $filesystem;
        $this->uploaderFactory   = $uploaderFactory;
        $this->transportBuilder  = $transportBuilder;
        $this->storeManager      = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig       = $scopeConfig;
    }

    public function execute()
    {
        $redirect = $this->redirectFactory->create()->setPath('gila-quote/index/index');

        // Enabled?
        if (!(bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return $redirect;
        }

        // CSRF
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key.'));
            return $redirect;
        }

        if (!$this->getRequest()->isPost()) {
            return $redirect;
        }

        try {
            // Gather & validate fields
            $r = $this->getRequest();
            $type     = trim((string)$r->getParam('type'));
            $company  = trim((string)$r->getParam('company_name'));
            $fullName = trim((string)$r->getParam('full_name'));
            $email    = trim((string)$r->getParam('email'));
            $phone    = trim((string)$r->getParam('phone'));
            $address  = trim((string)$r->getParam('address'));
            $details  = trim((string)$r->getParam('order_details'));

            if (!in_array($type, ['individual', 'company'], true)) {
                throw new LocalizedException(__('Please choose Individual or Company.'));
            }
            if ($type === 'company' && $company === '') {
                throw new LocalizedException(__('Company Name is required for Company type.'));
            }
            if ($fullName === '') {
                throw new LocalizedException(__('Full Name is required.'));
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new LocalizedException(__('Please enter a valid Email.'));
            }

            // Server-side phone validation: allow digits only if provided
            if ($phone !== '' && !preg_match('/^\d+$/', $phone)) {
                throw new LocalizedException(__('Please enter a valid numeric phone number.'));
            }

            // Optional upload
            $filePath = null; $fileName = null; $mimeType = null;
            if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
                $uploader = $this->uploaderFactory->create(['fileId' => 'attachment']);
                $uploader->setAllowedExtensions(['pdf', 'xls', 'xlsx', 'png', 'jpg', 'jpeg']);
                $uploader->setAllowRenameFiles(true);

                $varDir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                $target = 'tmp/ignitix_quoterequest';
                $varDir->create($target);

                $result = $uploader->save($varDir->getAbsolutePath($target));
                if (!$result) {
                    throw new LocalizedException(__('File upload failed.'));
                }
                $fileName = ltrim($result['file'], '/');
                $filePath = $varDir->getAbsolutePath($target . '/' . $fileName);

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeType = $ext === 'pdf' ? 'application/pdf'
                    : ($ext === 'xls' ? 'application/vnd.ms-excel'
                    : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }

            // Recipients (support multiple: comma/semicolon/space)
            $recipientsRaw = (string)$this->scopeConfig->getValue(self::XML_PATH_RECIPIENT, ScopeInterface::SCOPE_STORE);
            $emails = array_values(array_filter(preg_split('/[,\s;]+/', $recipientsRaw), static function ($e) {
                return $e !== '';
            }));
            if (empty($emails)) {
                throw new LocalizedException(__('Recipient email is not configured.'));
            }
            foreach ($emails as $addr) {
                if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                    throw new LocalizedException(__('Invalid recipient email: %1', $addr));
                }
            }

            // Build & send mail
            $storeId = (int)$this->storeManager->getStore()->getId();
            $this->inlineTranslation->suspend();

            $this->transportBuilder
                ->setTemplateIdentifier('ignitix_quoterequest_email_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars([
                    'type'          => $type,
                    'company_name'  => $company,
                    'full_name'     => $fullName,
                    'email'         => $email,
                    'phone'         => $phone,
                    'address'       => $address,
                    'order_details' => $details
                ])
                ->setFromByScope('general', $storeId)
                ->setReplyTo($email, $fullName);

            foreach ($emails as $addr) {
                $this->transportBuilder->addTo($addr);
            }

            // Attach the file BEFORE getTransport() (handled by our custom TransportBuilder)
            if ($filePath && is_readable($filePath)) {
                $content  = file_get_contents($filePath);
                $basename = basename($filePath);
                $this->transportBuilder->addAttachment($content, $mimeType, $basename);
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }

            $this->messageManager->addSuccessMessage(__('Your quote request has been sent. We will contact you soon.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to send your request: %1', $e->getMessage()));
        }

        return $redirect;
    }
}