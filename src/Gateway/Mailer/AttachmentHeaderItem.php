<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway\Mailer;

class AttachmentHeaderItem
{
    public function __construct(
        private string $voucher,
        private string|null $filename = null,
    ) {
    }

    public function getVoucher(): string
    {
        return $this->voucher;
    }

    public function getFilename(): string|null
    {
        return $this->filename;
    }

    public function toArray(): array
    {
        return [
            'voucher' => $this->getVoucher(),
            'filename' => $this->getFilename(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['voucher'], $data['filename']);
    }
}
