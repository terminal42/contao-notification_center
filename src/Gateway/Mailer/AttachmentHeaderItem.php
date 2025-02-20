<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway\Mailer;

class AttachmentHeaderItem
{
    public function __construct(
        private readonly string $voucher,
        private readonly string|null $filename = null,
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

    /**
     * @return array{voucher:string, filename:string}
     */
    public function toArray(): array
    {
        return [
            'voucher' => $this->getVoucher(),
            'filename' => $this->getFilename(),
        ];
    }

    /**
     * @param array{voucher:string, filename:string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['voucher'], $data['filename']);
    }
}
