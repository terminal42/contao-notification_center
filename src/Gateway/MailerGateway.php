<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\Filesystem\Dbafs\UnableToResolveUuidException;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\Validator;
use Soundasleep\Html2Text;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer\EmailStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;
use Terminal42\NotificationCenterBundle\Util\Stringable\FileUpload;

class MailerGateway extends AbstractGateway
{
    public const NAME = 'mailer';

    public function getName(): string
    {
        return self::NAME;
    }

    public function doSendParcel(Parcel $parcel): Receipt
    {
        $email = $parcel->getStamp(EmailStamp::class)->email;

        /** @var MailerInterface $mailer */
        $mailer = $this->serviceLocator->get('mailer');

        try {
            $mailer->send($email);

            return Receipt::createForSuccessfulDelivery($parcel);
        } catch (TransportExceptionInterface $e) {
            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfGatewayException(
                    self::NAME,
                    0,
                    $e
                )
            );
        }
    }

    protected function doFinalizeParcel(Parcel $parcel): Parcel
    {
        return $parcel->withJustOneStamp(new EmailStamp($this->createEmail($parcel)));
    }

    protected function getRequiredStampsForFinalization(): array
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

    private function createEmail(Parcel $parcel): Email
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;

        $email = (new Email())
            ->from($this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_sender_address')))
            ->to($this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('recipients')))
            ->subject($this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_subject')))
        ;

        if ('' !== ($cc = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_recipient_cc')))) {
            $email->cc($cc);
        }

        if ('' !== ($bcc = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_recipient_bcc')))) {
            $email->bcc($bcc);
        }

        if ('' !== ($replyTo = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_replyTo')))) {
            $email->replyTo($replyTo);
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

        $email->text($text);

        if (null !== $html) {
            $email->html($html);
        }

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
        $this->addAttachmentsFromBackend($languageConfig, $email);
        $this->addAttachmentsFromTokens($languageConfig, $parcel, $email);

        return $email;
    }

    private function renderEmailTemplate(Parcel $parcel): string
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;

        /** @var ContaoFramework $contaoFramework */
        $contaoFramework = $this->serviceLocator->get('contao.framework');
        $contaoFramework->initialize();

        $template = $contaoFramework->createInstance(FrontendTemplate::class, [$parcel->getMessageConfig()->getString('email_template')]);
        $template->charset = 'utf-8'; // @phpstan-ignore-line
        $template->title = $this->replaceTokensAndInsertTags($parcel, $languageConfig->getString('email_subject')); // @phpstan-ignore-line
        $template->css = ''; // @phpstan-ignore-line
        $template->body = $this->replaceTokensAndInsertTags($parcel, StringUtil::restoreBasicEntities($languageConfig->getString('email_html'))); // @phpstan-ignore-line

        return $template->parse();
    }

    private function addAttachmentsFromTokens(LanguageConfig $languageConfig, Parcel $parcel, Email $email): void
    {
        $tokens = StringUtil::trimsplit(',', $languageConfig->getString('attachment_tokens'));

        foreach ($tokens as $token) {
            $replaced = $this->replaceTokens($parcel, $token);

            try {
                $fileUpload = FileUpload::fromString($replaced);
            } catch (\Exception) {
                continue;
            }

            if (!file_exists($fileUpload->getTmpName())) {
                continue;
            }

            // Do not use attachFromPath() here. Finalizing the parcel requires all the data to be part
            // of the stamps which means we cannot store a path only, as that path might be gone in the future.
            $email->attach(file_get_contents($fileUpload->getTmpName()), $fileUpload->getName(), $fileUpload->getType());
        }
    }

    private function addAttachmentsFromBackend(LanguageConfig $languageConfig, Email $email): void
    {
        $attachments = StringUtil::deserialize($languageConfig->getString('attachments'), true);

        if (0 === \count($attachments)) {
            return;
        }

        /** @var VirtualFilesystem $vfs */
        $vfs = $this->serviceLocator->get('contao.files');

        // As soon as we're compatible with Contao >5.0 only, we can use the FilesystemUtil for this.
        foreach ($attachments as $uuid) {
            if (!\is_string($uuid)) {
                continue;
            }

            if (Validator::isBinaryUuid($uuid)) {
                $uuid = StringUtil::binToUuid($uuid);
            }

            try {
                $uuidObject = Uuid::isValid($uuid) ? Uuid::fromString($uuid) : Uuid::fromBinary($uuid);

                if (null === ($item = $vfs->get($uuidObject))) {
                    continue;
                }
            } catch (\InvalidArgumentException|UnableToResolveUuidException) {
                continue;
            }

            if (!$item->isFile()) {
                continue;
            }

            $email->attach($vfs->readStream($uuidObject), $item->getName(), $item->getMimeType());
        }
    }
}
