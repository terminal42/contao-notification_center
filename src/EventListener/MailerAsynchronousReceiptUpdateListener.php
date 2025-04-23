<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Receipt\AsynchronousReceipt;

class MailerAsynchronousReceiptUpdateListener
{
    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    #[AsEventListener]
    public function onSentMessage(SentMessageEvent $event): void
    {
        $email = $event->getMessage()->getOriginalMessage();

        if (!$email instanceof Email) {
            return;
        }

        $this->handleEmail($email);
    }

    #[AsEventListener]
    public function onFailedMessage(FailedMessageEvent $event): void
    {
        $email = $event->getMessage();

        if (!$email instanceof Email) {
            return;
        }

        $this->handleEmail($email, $event->getError());
    }

    private function handleEmail(Email $email, \Throwable|null $error = null): void
    {
        $messageId = $email->getHeaders()->get(MailerGateway::MESSAGE_IDENTIFIER_HEADER)?->getBodyAsString();
        $email->getHeaders()->remove(MailerGateway::MESSAGE_IDENTIFIER_HEADER);

        if (!$messageId) {
            return;
        }

        $receipt = $error
            ? AsynchronousReceipt::createForUnsuccessfulDelivery($messageId, $error)
            : AsynchronousReceipt::createForSuccessfulDelivery($messageId);

        $this->notificationCenter->informAboutAsynchronousReceipt($receipt);
    }
}
