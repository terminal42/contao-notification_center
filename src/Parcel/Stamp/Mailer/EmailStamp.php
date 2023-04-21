<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp\Mailer;

use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Util\Email as EmailUtil;

class EmailStamp implements StampInterface
{
    private string $from = '';
    private string $to = '';
    private string $subject = '';
    private string $cc = '';
    private string $bcc = '';
    private string $replyTo = '';
    private string $text = '';
    private string $html = '';
    private array $attachmentVouchers = [];

    public function withFrom(string $from): self
    {
        $clone = clone $this;
        $clone->from = $from;

        return $clone;
    }

    public function withTo(string $to): self
    {
        $clone = clone $this;
        $clone->to = $to;

        return $clone;
    }

    public function withSubject(string $subject): self
    {
        $clone = clone $this;
        $clone->subject = $subject;

        return $clone;
    }

    public function withCc(string $cc): self
    {
        $clone = clone $this;
        $clone->cc = $cc;

        return $clone;
    }

    public function withBcc(string $bcc): self
    {
        $clone = clone $this;
        $clone->bcc = $bcc;

        return $clone;
    }

    public function withReplyTo(string $replyTo): self
    {
        $clone = clone $this;
        $clone->replyTo = $replyTo;

        return $clone;
    }

    public function withText(string $text): self
    {
        $clone = clone $this;
        $clone->text = $text;

        return $clone;
    }

    public function withHtml(string $html): self
    {
        $clone = clone $this;
        $clone->html = $html;

        return $clone;
    }

    public function withAttachmentVoucher(string $voucher): self
    {
        $clone = clone $this;
        $clone->attachmentVouchers[] = $voucher;

        return $clone;
    }

    public function getAttachmentVouchers(): array
    {
        return $this->attachmentVouchers;
    }

    public function applyToEmail(Email $email): void
    {
        if ($this->from) {
            $email->from($this->from);
        }

        if ($this->to) {
            $email->to(...EmailUtil::splitEmailAddresses($this->to));
        }

        if ($this->subject) {
            $email->subject($this->subject);
        }

        if ($this->cc) {
            $email->cc(...EmailUtil::splitEmailAddresses($this->cc));
        }

        if ($this->bcc) {
            $email->bcc(...EmailUtil::splitEmailAddresses($this->bcc));
        }

        if ($this->replyTo) {
            $email->replyTo($this->replyTo);
        }

        if ($this->text) {
            $email->text($this->text);
        }

        if ($this->html) {
            $email->html($this->html);
        }
    }

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'subject' => $this->subject,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'replyTo' => $this->replyTo,
            'text' => $this->text,
            'html' => $this->html,
            'attachmentVouchers' => $this->attachmentVouchers,
        ];
    }

    public static function fromArray(array $data): StampInterface
    {
        $stamp = (new self())
            ->withFrom($data['from'])
            ->withTo($data['to'])
            ->withSubject($data['subject'])
            ->withCc($data['cc'])
            ->withBcc($data['bcc'])
            ->withReplyTo($data['replyTo'])
            ->withText($data['text'])
            ->withHtml($data['html'])
        ;

        foreach ($data['attachmentVouchers'] as $voucher) {
            $stamp = $stamp->withAttachmentVoucher($voucher);
        }

        return $stamp;
    }
}
