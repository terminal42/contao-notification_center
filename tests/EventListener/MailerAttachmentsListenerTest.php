<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\EventListener\MailerAttachmentsListener;
use Terminal42\NotificationCenterBundle\Gateway\Mailer\AttachmentHeaderItem;
use Terminal42\NotificationCenterBundle\Gateway\Mailer\BulkyItemStorageAttachmentsHeader;

class MailerAttachmentsListenerTest extends TestCase
{
    public function testInvokeWithQueuedMessageDoesNothing(): void
    {
        $message = $this->createMock(Message::class);
        $message
            ->expects($this->never())
            ->method('getHeaders')
        ;

        $bulkyItemStorage = $this->createMock(BulkyItemStorage::class);
        $listener = new MailerAttachmentsListener($bulkyItemStorage);

        $event = new MessageEvent($message, $this->createMock(Envelope::class), 'transport', true);

        $listener($event);
    }

    public function testInvokeWithNonEmailMessageDoesNothing(): void
    {
        $message = $this->createMock(Message::class);
        $message
            ->expects($this->never())
            ->method('getHeaders')
        ;

        $bulkyItemStorage = $this->createMock(BulkyItemStorage::class);
        $listener = new MailerAttachmentsListener($bulkyItemStorage);

        $event = new MessageEvent($message, $this->createMock(Envelope::class), 'transport', false); // not queued

        $listener($event);
    }

    public function testInvokeWithoutAttachmentsHeaderDoesNothing(): void
    {
        $email = new Email();

        $bulkyItemStorage = $this->createMock(BulkyItemStorage::class);
        $bulkyItemStorage
            ->expects($this->never())
            ->method('retrieve')
        ;

        $listener = new MailerAttachmentsListener($bulkyItemStorage);

        $event = new MessageEvent($email, $this->createMock(Envelope::class), 'transport', false); // not queued

        $listener($event);
    }

    public function testInvokeWithAttachmentsHeaderAddsAttachmentsAndRemovesHeader(): void
    {
        $email = new Email();
        $header = new BulkyItemStorageAttachmentsHeader(MailerAttachmentsListener::ATTACHMENTS_HEADER_NAME);
        $header->addAttachmentItem(new AttachmentHeaderItem('voucher-id-1'));
        $header->addAttachmentItem(new AttachmentHeaderItem('voucher-id-2', 'different-filename.jpg'));
        $email->getHeaders()->add($header);

        $bulkyItemStorage = $this->createMock(BulkyItemStorage::class);
        $bulkyItemStorage
            ->expects($this->exactly(2))
            ->method('retrieve')
            ->willReturnMap([
                ['voucher-id-1', FileItem::fromPath(__DIR__.'/../Fixtures/name.jpg', 'original-1.jpg', 'image/jpg', 0)],
                ['voucher-id-2', FileItem::fromPath(__DIR__.'/../Fixtures/name.jpg', 'original-2.jpg', 'image/jpg', 0)],
            ])
        ;

        $listener = new MailerAttachmentsListener($bulkyItemStorage);

        $event = new MessageEvent($email, $this->createMock(Envelope::class), 'transport', false); // not queued

        $listener($event);

        $this->assertCount(2, $email->getAttachments());
        $this->assertSame('original-1.jpg', $email->getAttachments()[0]->getFilename());
        $this->assertSame('different-filename.jpg', $email->getAttachments()[1]->getFilename());

        // Header must have been removed now
        $this->assertFalse($email->getHeaders()->has(MailerAttachmentsListener::ATTACHMENTS_HEADER_NAME));
    }
}
