<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\Controller;
use Contao\CoreBundle\Filesystem\Dbafs\UnableToResolveUuidException;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\Validator;
use Soundasleep\Html2Text;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\EventListener\MailerAttachmentsListener;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Gateway\Mailer\AttachmentHeaderItem;
use Terminal42\NotificationCenterBundle\Gateway\Mailer\BulkyItemStorageAttachmentsHeader;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\AsynchronousDeliveryStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer\BackendAttachmentsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer\EmailStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

class MailerGateway extends AbstractGateway
{
    public const NAME = 'mailer';

    public const MESSAGE_IDENTIFIER_HEADER = 'Notification-Center-Parcel-ID';

    public function __construct(
        private readonly ContaoFramework $contaoFramework,
        private readonly VirtualFilesystemInterface $filesStorage,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function doSendParcel(Parcel $parcel): Receipt
    {
        try {
            $this->mailer->send($this->createEmail($parcel));

            return Receipt::createForSuccessfulDelivery($parcel);
        } catch (\Throwable $e) {
            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfGatewayException(
                    self::NAME,
                    0,
                    $e,
                ),
            );
        }
    }

    protected function doSealParcel(Parcel $parcel): Parcel
    {
        // Copy back end attachments so that they are still there if being removed from
        // the back end. Sealing means ensuring that the parcel has all its content.
        $parcel = $this->copyBackendAttachments($parcel, $parcel->getStamp(LanguageConfigStamp::class)->languageConfig);

        return $parcel
            ->seal()
            ->withStamp(AsynchronousDeliveryStamp::createWithRandomId())
            ->withStamp($this->createEmailStamp($parcel))
        ;
    }

    protected function getRequiredStampsForSealing(): array
    {
        return [
            LanguageConfigStamp::class,
        ];
    }

    protected function getRequiredStampsForSending(): array
    {
        return [
            EmailStamp::class,
        ];
    }

    private function createEmailStamp(Parcel $parcel): EmailStamp
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;

        $stamp = new EmailStamp();

        $stamp = $stamp->withTo($this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('recipients')));
        $stamp = $stamp->withSubject($this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_subject')));

        // Automatically fall back to ##admin_email## if no sender was configured. In
        // case this token does not exist either, it will result in the same error as not
        // supplying a sender address at all.
        $from = '' !== $languageConfig->getString('email_sender_address') ? $languageConfig->getString('email_sender_address') : '##admin_email##';

        if ('' !== ($from = $this->replaceTokensAndInsertTags($parcel, $from))) {
            $stamp = $stamp->withFrom($from);
        }

        $fromName = '' !== $languageConfig->getString('email_sender_name') ? $languageConfig->getString('email_sender_name') : '##admin_name##';

        if ('' !== ($fromName = $this->replaceTokensAndInsertTags($parcel, $fromName))) {
            $stamp = $stamp->withFromName($fromName);
        }

        if ('' !== ($cc = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_recipient_cc')))) {
            $stamp = $stamp->withCc($cc);
        }

        if ('' !== ($bcc = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_recipient_bcc')))) {
            $stamp = $stamp->withBcc($bcc);
        }

        if ('' !== ($replyTo = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_replyTo')))) {
            $stamp = $stamp->withReplyTo($replyTo);
        }

        $text = '';
        $html = null;

        switch ($languageConfig->getString('email_mode')) {
            case 'textOnly':
                $text = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_text'));
                break;
            case 'htmlAndAutoText':
                $html = $this->renderEmailTemplate($parcel, $stamp);
                $text = Html2Text::convert($html, ['ignore_errors' => true]);
                break;
            case 'textAndHtml':
                $html = $this->renderEmailTemplate($parcel, $stamp);
                $text = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_text'));
                break;
        }

        $stamp = $stamp->withText($text);

        if ($html) {
            $stamp = $stamp->withHtml($html);
        }

        $stamp = $this->addAttachmentsFromBackend($parcel, $stamp);

        return $this->addAttachmentsFromTokens($languageConfig, $parcel, $stamp);
    }

    private function createEmail(Parcel $parcel): Email
    {
        /** @var EmailStamp $emailStamp */
        $emailStamp = $parcel->getStamp(EmailStamp::class);

        $email = new Email();
        $emailStamp->applyToEmail($email);

        // Validate the e-mail so we throw early enough
        $email->ensureValidity();

        // Adjust the priority if configured to do so
        if (($priority = $parcel->getMessageConfig()->getInt('email_priority')) > 0) {
            $email = $email->priority($priority);
        }

        // Adjust the transport if configured to do so
        if (null !== ($gatewayConfigStamp = $parcel->getStamp(GatewayConfigStamp::class))) {
            if (null !== ($transport = $gatewayConfigStamp->gatewayConfig->get('mailerTransport'))) {
                $email->getHeaders()->addTextHeader('X-Transport', $transport);
            }
        }

        // Add the async delivery stamp
        if (null !== ($asyncDeliveryStamp = $parcel->getStamp(AsynchronousDeliveryStamp::class))) {
            $email->getHeaders()->addTextHeader(self::MESSAGE_IDENTIFIER_HEADER, $asyncDeliveryStamp->identifier);
        }

        // Attachment header items
        if ($email->getHeaders()->has(MailerAttachmentsListener::ATTACHMENTS_HEADER_NAME)) {
            /** @var BulkyItemStorageAttachmentsHeader $attachments */
            $attachments = $email->getHeaders()->get(MailerAttachmentsListener::ATTACHMENTS_HEADER_NAME);
        } else {
            $attachments = new BulkyItemStorageAttachmentsHeader(MailerAttachmentsListener::ATTACHMENTS_HEADER_NAME);
        }

        foreach ($emailStamp->getAttachmentVouchers() as $voucher) {
            $attachments->addAttachmentItem(new AttachmentHeaderItem($voucher));
        }

        // Embedded images
        foreach ($emailStamp->getEmbeddedImageVouchers() as $voucher) {
            $attachments->addAttachmentItem(new AttachmentHeaderItem($voucher, $this->encodeVoucherForContentId($voucher)));
        }

        $email->getHeaders()->add($attachments);

        return $email;
    }

    private function renderEmailTemplate(Parcel $parcel, EmailStamp &$stamp): string
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;
        $tokenCollection = $parcel->getStamp(TokenCollectionStamp::class)?->tokenCollection;

        $this->contaoFramework->initialize();

        $template = $this->contaoFramework->createInstance(FrontendTemplate::class, [$parcel->getMessageConfig()->getString('email_template')]);
        $template->charset = 'utf-8';
        $template->title = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_subject'));
        $template->css = '';
        $template->body = $this->replaceTokensAndInsertTags($parcel, StringUtil::restoreBasicEntities($languageConfig->getString('email_html')));
        $template->language = LocaleUtil::formatAsLanguageTag($languageConfig->getString('language'));
        $template->parsedTokens = null === $tokenCollection ? [] : $tokenCollection->forSimpleTokenParser();
        $template->rawTokens = $tokenCollection;

        $html = $this->replaceInsertTags($template->parse());

        // Embed images before making URLs absolute
        $html = $this->embedImages($html, $stamp);

        return $this->contaoFramework->getAdapter(Controller::class)->convertRelativeUrls($html);
    }

    private function addAttachmentsFromTokens(LanguageConfig $languageConfig, Parcel $parcel, EmailStamp $emailStamp): EmailStamp
    {
        $tokens = StringUtil::trimsplit(',', $languageConfig->getString('attachment_tokens'));

        foreach ($tokens as $token) {
            $vouchers = StringUtil::trimsplit(',', $this->replaceTokens($parcel, $token));

            foreach ($vouchers as $voucher) {
                if ($this->isBulkyItemVoucher($parcel, $voucher)) {
                    $emailStamp = $emailStamp->withAttachmentVoucher($voucher);
                }
            }
        }

        return $emailStamp;
    }

    private function addAttachmentsFromBackend(Parcel $parcel, EmailStamp $emailStamp): EmailStamp
    {
        if (!$parcel->hasStamp(BackendAttachmentsStamp::class)) {
            return $emailStamp;
        }

        foreach ($parcel->getStamp(BackendAttachmentsStamp::class)->toArray() as $voucher) {
            $emailStamp = $emailStamp->withAttachmentVoucher($voucher);
        }

        return $emailStamp;
    }

    private function copyBackendAttachments(Parcel $parcel, LanguageConfig $languageConfig): Parcel
    {
        // Attachments have been added before (by e.g. a third party logic)
        if ($parcel->hasStamp(BackendAttachmentsStamp::class)) {
            return $parcel;
        }

        $attachments = StringUtil::deserialize($languageConfig->getString('attachments'), true);

        if (0 === \count($attachments)) {
            return $parcel;
        }

        $vouchers = [];

        // As soon as we're compatible with Contao >5.0 only, we can use the
        // FilesystemUtil for this.
        foreach ($attachments as $uuid) {
            if (!\is_string($uuid)) {
                continue;
            }

            if (Validator::isBinaryUuid($uuid)) {
                $uuid = StringUtil::binToUuid($uuid);
            }

            try {
                $uuidObject = Uuid::isValid($uuid) ? Uuid::fromString($uuid) : Uuid::fromBinary($uuid);
            } catch (\InvalidArgumentException|UnableToResolveUuidException) {
                continue;
            }

            $voucher = $this->createBulkyItemStorageVoucher($uuidObject, $this->filesStorage);

            if (null === $voucher) {
                continue;
            }

            $vouchers[] = $voucher;
        }

        if (0 === \count($vouchers)) {
            return $parcel;
        }

        return $parcel->withStamp(new BackendAttachmentsStamp($vouchers));
    }

    private function createBulkyItemStorageVoucher(Uuid|string $location, VirtualFilesystemInterface $filesystem): string|null
    {
        try {
            if (null === ($item = $filesystem->get($location))) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        if (!$item->isFile()) {
            return null;
        }

        return $this->getBulkyItemStorage()?->store(
            FileItem::fromStream(
                $filesystem->readStream($location),
                $item->getName(),
                $item->getMimeType(),
                $item->getFileSize(),
            ),
        );
    }

    private function encodeVoucherForContentId(string $voucher): string
    {
        return rawurlencode($voucher);
    }

    private function embedImages(string $html, EmailStamp &$stamp): string
    {
        $prefixToStrip = '';

        if (method_exists($this->filesStorage, 'getPrefix')) {
            $prefixToStrip = $this->filesStorage->getPrefix();
        }

        return preg_replace_callback(
            '/<[a-z][a-z0-9]*\b[^>]*((src=|background=|url\()["\']??)(.+\.(jpe?g|png|gif|bmp|tiff?|swf))(["\' ]??(\)??))[^>]*>/Ui',
            function ($matches) use (&$stamp, $prefixToStrip) {
                $location = ltrim(ltrim(ltrim($matches[3], '/'), $prefixToStrip), '/');
                $voucher = $this->createBulkyItemStorageVoucher($location, $this->filesStorage);

                if (null === $voucher) {
                    return $matches[0];
                }

                $stamp = $stamp->withEmbeddedImageVoucher($voucher);

                return str_replace($matches[3], 'cid:'.$this->encodeVoucherForContentId($voucher), $matches[0]);
            },
            $html,
        );
    }
}
