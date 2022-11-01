<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Soundasleep\Html2Text;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

class MailerGateway extends AbstractGateway
{
    public const NAME = 'mailer';

    public function getName(): string
    {
        return self::NAME;
    }

    public function doSendParcel(Parcel $parcel): Receipt
    {
        $email = $this->createEmail($parcel);

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
                    null,
                    $e
                )
            );
        }
    }

    private function createEmail(Parcel $parcel): Email
    {
        $languageConfig = $parcel->getStamp(LanguageConfigStamp::class)->languageConfig;

        $email = (new Email())
            ->from($this->replaceTokens($parcel, $languageConfig->getString('email_sender_address')))
            ->to($this->replaceTokens($parcel, $languageConfig->getString('recipients')))
            ->subject($this->replaceTokens($parcel, $languageConfig->getString('email_subject')))
        ;

        if ('' !== ($cc = $this->replaceTokens($parcel, $languageConfig->getString('email_recipient_cc')))) {
            $email->cc($cc);
        }

        if ('' !== ($bcc = $this->replaceTokens($parcel, $languageConfig->getString('email_recipient_bcc')))) {
            $email->bcc($bcc);
        }

        if ('' !== ($replyTo = $this->replaceTokens($parcel, $languageConfig->getString('email_replyTo')))) {
            $email->replyTo($replyTo);
        }

        $text = '';
        $html = null;

        switch ($languageConfig->getString('email_mode')) {
            case 'textOnly':
                $text = $this->replaceTokens($parcel, $languageConfig->getString('email_text'));
                break;
            case 'htmlAndAutoText':
                $html = $this->renderEmailTemplate($parcel);
                $text = Html2Text::convert($html);
                break;
            case 'textAndHtml':
                $html = $this->renderEmailTemplate($parcel);
                $text = $this->replaceTokens($parcel, $languageConfig->getString('email_text'));
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
        $template->title = $this->replaceTokens($parcel, $languageConfig->getString('email_subject')); // @phpstan-ignore-line
        $template->css = ''; // @phpstan-ignore-line
        $template->body = $this->replaceTokens($parcel, $languageConfig->getString('email_html')); // @phpstan-ignore-line

        return $template->parse();
    }

    protected function getRequiredStamps(): array
    {
        return [
            LanguageConfigStamp::class,
        ];
    }
}
