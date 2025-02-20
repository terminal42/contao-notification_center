<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\Gateway\Mailer\BulkyItemStorageAttachmentsHeader;

#[AsEventListener]
class MailerAttachmentsListener
{
    public const ATTACHMENTS_HEADER_NAME = 'Notification-Center-Bulky-Item-Storage-Attachments';

    public function __construct(private readonly BulkyItemStorage $bulkyItemStorage)
    {
    }

    public function __invoke(MessageEvent $event): void
    {
        // Message is queued, we don't want to attach anything but keep our headers to prevent sending huge amounts
        // of data with the message.
        if ($event->isQueued()) {
            return;
        }

        $email = $event->getMessage();

        if (!$email instanceof Email) {
            return;
        }

        $attachmentsHeader = $email->getHeaders()->get(self::ATTACHMENTS_HEADER_NAME);

        if (!$attachmentsHeader instanceof BulkyItemStorageAttachmentsHeader) {
            return;
        }

        foreach ($attachmentsHeader->getAttachmentItems() as $attachmentItem) {
            $item = $this->bulkyItemStorage->retrieve($attachmentItem->getVoucher());

            if ($item instanceof FileItem) {
                $email->attach(
                    $item->getContents(),
                    $attachmentItem->getFilename() ?? $item->getName(),
                    $item->getMimeType(),
                );
            }
        }

        $email->getHeaders()->remove(self::ATTACHMENTS_HEADER_NAME);
    }
}
