<?php
namespace Ignitix\QuoteRequest\Model\Mail\Template;

use Magento\Framework\Mail\Template\TransportBuilder as CoreTransportBuilder;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mime\Message as MimeMessage;

class TransportBuilder extends CoreTransportBuilder
{
    /** @var MimePart[] */
    private $attachments = [];

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null
    ) {
        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory, $messageFactory);
    }

    /**
     * Add an attachment (raw content).
     */
    public function addAttachment(
        string $content,
        string $mimeType = Mime::TYPE_OCTETSTREAM,
        string $filename = null,
        string $disposition = Mime::DISPOSITION_ATTACHMENT,
        string $encoding = Mime::ENCODING_BASE64
    ): self {
        $part = new MimePart($content);
        $part->type        = $mimeType;
        $part->disposition = $disposition;
        $part->encoding    = $encoding;
        if ($filename) {
            $part->filename = $filename;
        }
        $this->attachments[] = $part;
        return $this;
    }

    /**
     * Ensure attachments are merged into the email body before sending.
     */
    protected function prepareMessage(): self
    {
        parent::prepareMessage();

        // $this->message may contain a string (rendered HTML) or an existing MimeMessage
        $body = $this->message->getBody();

        if ($body instanceof MimeMessage) {
            // add our parts to the existing multipart
            foreach ($this->attachments as $part) {
                $body->addPart($part);
            }
            $this->message->setBody($body);
        } else {
            // build a new multipart/mixed: html + attachments
            $mimeMessage = new MimeMessage();

            // HTML body as first part
            $htmlPart = new MimePart((string)$body);
            $htmlPart->type        = 'text/html; charset=UTF-8';
            $htmlPart->encoding    = Mime::ENCODING_QUOTEDPRINTABLE;
            $htmlPart->disposition = Mime::DISPOSITION_INLINE;

            $parts = array_merge([$htmlPart], $this->attachments);
            $mimeMessage->setParts($parts);

            $this->message->setBody($mimeMessage);

            // set correct header
            $headers = $this->message->getHeaders();
            if ($headers->has('Content-Type')) {
                $headers->removeHeader('Content-Type');
            }
            $headers->addHeaderLine(
                'Content-Type',
                'multipart/mixed; boundary="' . $mimeMessage->getMime()->boundary() . '"'
            );
        }

        return $this;
    }
}