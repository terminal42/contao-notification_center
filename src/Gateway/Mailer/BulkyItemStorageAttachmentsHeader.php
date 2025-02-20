<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway\Mailer;

use Symfony\Component\Mime\Header\AbstractHeader;

class BulkyItemStorageAttachmentsHeader extends AbstractHeader
{
    /**
     * @var array<AttachmentHeaderItem>
     */
    private $attachmentItems = [];

    public function setBody(mixed $body): void
    {
        if (!\is_string($body)) {
            throw new \InvalidArgumentException('$body must be a string');
        }

        $data = json_decode($body, true);

        foreach ($data as $item) {
            $this->attachmentItems[] = AttachmentHeaderItem::fromArray($item);
        }
    }

    public function getBody(): string
    {
        return $this->getBodyAsString();
    }

    public function getBodyAsString(): string
    {
        $items = [];

        foreach ($this->attachmentItems as $attachmentItem) {
            $items[] = $attachmentItem->toArray();
        }

        return json_encode($items);
    }

    /**
     * @return array<AttachmentHeaderItem>
     */
    public function getAttachmentItems(): array
    {
        return $this->attachmentItems;
    }

    public function addAttachmentItem(AttachmentHeaderItem $attachmentItem): self
    {
        $this->attachmentItems[] = $attachmentItem;

        return $this;
    }
}
