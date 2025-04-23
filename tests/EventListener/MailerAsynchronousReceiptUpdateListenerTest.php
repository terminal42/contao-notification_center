<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Terminal42\NotificationCenterBundle\EventListener\MailerAsynchronousReceiptUpdateListener;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Receipt\AsynchronousReceipt;

class MailerAsynchronousReceiptUpdateListenerTest extends TestCase
{
    protected function setUp(): void
    {
        if (version_compare(InstalledVersions::getVersion('contao/core-bundle'), '5.0.0', '<')) {
            $this->markTestSkipped('Asynchronous mailer updates are only supported as of Contao 5. In Contao 4.13 they are always synchronous anyway.');
        }
    }

    public function testOnSentMessageWithValidEmail(): void
    {
        $notificationCenter = $this->createMock(NotificationCenter::class);
        $listener = new MailerAsynchronousReceiptUpdateListener($notificationCenter);

        $email = new Email();
        $identifier = 'test-identifier';
        $email->getHeaders()->addTextHeader(MailerGateway::MESSAGE_IDENTIFIER_HEADER, $identifier);

        $sentMessage = $this->createMock(SentMessage::class);
        $sentMessage
            ->expects($this->once())
            ->method('getOriginalMessage')
            ->willReturn($email)
        ;

        $event = new SentMessageEvent($sentMessage);

        $notificationCenter
            ->expects($this->once())
            ->method('informAboutAsynchronousReceipt')
            ->with($this->callback(static fn (AsynchronousReceipt $receipt) => $receipt->getIdentifier() === $identifier && true === $receipt->wasDelivered()))
        ;

        $listener->onSentMessage($event);
    }

    public function testOnFailedMessage(): void
    {
        $notificationCenter = $this->createMock(NotificationCenter::class);
        $listener = new MailerAsynchronousReceiptUpdateListener($notificationCenter);

        $email = new Email();
        $identifier = 'fail-identifier';
        $email->getHeaders()->addTextHeader(MailerGateway::MESSAGE_IDENTIFIER_HEADER, $identifier);

        $exception = new \Exception('Delivery failed');
        $event = new FailedMessageEvent($email, $exception);

        $notificationCenter
            ->expects($this->once())
            ->method('informAboutAsynchronousReceipt')
            ->with($this->callback(static fn (AsynchronousReceipt $receipt) => $receipt->getIdentifier() === $identifier
                    && false === $receipt->wasDelivered()
                    && $exception === $receipt->getException(),
            ))
        ;

        $listener->onFailedMessage($event);
    }

    public function testIgnoresNonEmailMessages(): void
    {
        $notificationCenter = $this->createMock(NotificationCenter::class);
        $notificationCenter
            ->expects($this->never())
            ->method('informAboutAsynchronousReceipt')
        ;

        $listener = new MailerAsynchronousReceiptUpdateListener($notificationCenter);

        $sentMessage = $this->createMock(SentMessage::class);
        $sentMessage
            ->expects($this->once())
            ->method('getOriginalMessage')
            ->willReturn(new Message())
        ;

        $event = new SentMessageEvent($sentMessage);
        $listener->onSentMessage($event);
    }

    public function testSkipsWhenIdentifierHeaderMissing(): void
    {
        $notificationCenter = $this->createMock(NotificationCenter::class);
        $notificationCenter
            ->expects($this->never())
            ->method('informAboutAsynchronousReceipt')
        ;

        $listener = new MailerAsynchronousReceiptUpdateListener($notificationCenter);

        $email = new Email(); // No header added

        $sentMessage = $this->createMock(SentMessage::class);
        $sentMessage
            ->method('getOriginalMessage')
            ->willReturn($email)
        ;
        $event = new SentMessageEvent($sentMessage);

        $listener->onSentMessage($event);
    }
}
