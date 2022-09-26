<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\FrontendTemplate;
use Soundasleep\Html2Text;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Config\GatewayConfig;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Parcel\MailerParcel;
use Terminal42\NotificationCenterBundle\Parcel\ParcelInterface;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class MailerGateway implements GatewayInterface
{
    public const NAME = 'mailer';

    public function __construct(private MailerInterface $mailer, private SimpleTokenParser $simpleTokenParser, private ContaoFramework $framework)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function sendParcel(ParcelInterface $parcel): void
    {
        if (!$parcel instanceof MailerParcel) {
            return;
        }

        $this->mailer->send($parcel->email); // TODO: exception handling
    }

    public function createParcelFromConfigs(TokenCollection $tokenCollection, NotificationConfig $notificationConfig, MessageConfig $messageConfig, GatewayConfig $gatewayConfig, LanguageConfig $languageConfig = null): ParcelInterface
    {
        if (null === $languageConfig) {
            // TODO: exception? result?
        }

        $tokens = $tokenCollection->asRawKeyValueWithStringsOnly();

        $email = (new Email())
            ->from($this->simpleTokenParser->parse($languageConfig->getString('email_sender_address'), $tokens))
            ->to($this->simpleTokenParser->parse($languageConfig->getString('recipients'), $tokens))
            ->priority($messageConfig->getInt('email_priority'))
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

        $text = '';
        $html = null;

        switch ($languageConfig->getString('email_mode')) {
            case 'textOnly':
                $text = $this->simpleTokenParser->parse($languageConfig->getString('email_text'), $tokens);
                break;
            case 'htmlAndAutoText':
                $html = $this->renderEmailTemplate($languageConfig, $messageConfig, $tokens);
                $text = Html2Text::convert($html);
                break;
            case 'textAndHtml':
                $html = $this->renderEmailTemplate($languageConfig, $messageConfig, $tokens);
                $text = $this->simpleTokenParser->parse($languageConfig->getString('email_text'), $tokens);
                break;
        }

        $email->text($text);

        if (null !== $html) {
            $email->html($html);
        }

        // Adjust the transport if configured to do so
        if (null !== ($transport = $gatewayConfig->get('mailerTransport'))) {
            $email->getHeaders()->addTextHeader('X-Transport', $transport);
        }

        return new MailerParcel($email);
    }

    /**
     * @param array<string, string> $tokens
     */
    private function renderEmailTemplate(LanguageConfig $languageConfig, MessageConfig $messageConfig, array $tokens): string
    {
        $this->framework->initialize();
        $template = $this->framework->createInstance(FrontendTemplate::class, [$messageConfig->getString('email_template')]);
        $template->charset = 'utf-8'; // @phpstan-ignore-line
        $template->title = $this->simpleTokenParser->parse($languageConfig->getString('email_subject'), $tokens); // @phpstan-ignore-line
        $template->css = ''; // @phpstan-ignore-line
        $template->body = $this->simpleTokenParser->parse($languageConfig->getString('email_html'), $tokens); // @phpstan-ignore-line

        return $template->parse();
    }

    public function getParcelClass(): string
    {
        return MailerParcel::class;
    }
}
