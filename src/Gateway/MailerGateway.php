<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\String\SimpleTokenParser;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;

class MailerGateway implements GatewayInterface
{
    public const NAME = 'mailer';

    public function __construct(private MailerInterface $mailer, private SimpleTokenParser $simpleTokenParser)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function sendParcel(Parcel $parcel): void
    {
        $languageConfig = $parcel->languageConfig;

        if (null === $languageConfig) {
            // TODO: exception? result?
        }

        $email = $this->prepareEmail($parcel, $languageConfig);

        // Adjust the transport if configured to do so
        if (null !== ($transport = $parcel->gatewayConfig->get('mailerTransport'))) {
            $email->getHeaders()->addTextHeader('X-Transport', $transport);
        }

        $this->mailer->send($email); // TODO: exception handling
    }

    private function prepareEmail(Parcel $parcel, LanguageConfig $languageConfig): Email
    {
        $tokens = $parcel->tokenCollection->asRawKeyValueWithStringsOnly();

        $email = (new Email())
            ->from($this->simpleTokenParser->parse($languageConfig->getString('email_sender_address'), $tokens))
            ->to($this->simpleTokenParser->parse($languageConfig->getString('recipients'), $tokens))
            ->priority($parcel->messageConfig->getInt('email_priority'))
            ->subject($this->simpleTokenParser->parse($languageConfig->getString('email_subject'), $tokens))
        ;

        if ('' !== ($cc = $this->simpleTokenParser->parse($languageConfig->getString('email_recipient_cc'), $tokens))) {
            $email->cc($cc);
        }

        if ('' !== ($bcc = $this->simpleTokenParser->parse($languageConfig->getString('email_recipient_bcc'), $tokens))) {
            $email->bcc($bcc);
        }

        if ('' !== ($replyTo = $this->simpleTokenParser->parse($languageConfig->getString('email_replyTo'), $tokens))) {
            $email->replyTo($replyTo);
        }

        $mode = $languageConfig->getString('email_mode');

        $email->text('Foobar'); // TODO: implement
        $email->html('<p>Foobar</p>'); // TODO: implement

        return $email;
    }
}
