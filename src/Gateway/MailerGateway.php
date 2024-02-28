<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\Filesystem\Dbafs\UnableToResolveUuidException;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
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
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer\BackendAttachmentsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer\EmailStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

class MailerGateway extends AbstractGateway
{
    public const NAME = 'mailer';

    public function __construct(
        private readonly ContaoFramework $contaoFramework,
        private readonly VirtualFilesystem $filesystem,
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

        if ('' !== ($from = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_sender_address')))) {
            $stamp = $stamp->withFrom($from);
        }

        if ('' !== ($fromName = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_sender_name')))) {
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
                $html = $this->renderEmailTemplate($parcel);
                $text = Html2Text::convert($html);
                break;
            case 'textAndHtml':
                $html = $this->renderEmailTemplate($parcel);
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

        // Attachments
        foreach ($emailStamp->getAttachmentVouchers() as $voucher) {
            $item = $this->getNotificationCenter()->getBulkyGoodsStorage()->retrieve($voucher);

            if ($item instanceof FileItem) {
                $email->attach(
                    $item->getContents(),
                    $item->getName(),
                    $item->getMimeType(),
                );
            }
        }

        return $email;
    }

    private function renderEmailTemplate(Parcel $parcel): string
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;

        $this->contaoFramework->initialize();

        $template = $this->contaoFramework->createInstance(FrontendTemplate::class, [$parcel->getMessageConfig()->getString('email_template')]);
        $template->charset = 'utf-8';
        $template->title = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_subject'));
        $template->css = '';
        $template->body = $this->replaceTokensAndInsertTags($parcel, StringUtil::restoreBasicEntities($languageConfig->getString('email_html')));
        $template->language = LocaleUtil::formatAsLanguageTag($languageConfig->getString('language'));

        return $template->parse();
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

                if (null === ($item = $this->filesystem->get($uuidObject))) {
                    continue;
                }
            } catch (\InvalidArgumentException|UnableToResolveUuidException) {
                continue;
            }

            if (!$item->isFile()) {
                continue;
            }

            $voucher = $this->getNotificationCenter()?->getBulkyGoodsStorage()->store(
                FileItem::fromStream(
                    $this->filesystem->readStream($uuidObject),
                    $item->getName(),
                    $item->getMimeType(),
                    $item->getFileSize(),
                ),
            );

            if (null !== $voucher) {
                $vouchers[] = $voucher;
            }
        }

        if (0 === \count($vouchers)) {
            return $parcel;
        }

        return $parcel->withStamp(new BackendAttachmentsStamp($vouchers));
    }
}
